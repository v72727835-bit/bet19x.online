<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Moves credit or commission balance from one agent to another
 * (e.g. master agent to master agent).
 *
 * - credit:  only the sender's unused credit (limit - used) can move; the
 *            sender's credit limit goes down and the receiver's goes up.
 * - balance: the agents' commission balance (wallet_balance).
 *
 * Every transfer is written to the audit log under module "Agent Transfer",
 * which is also what the history tables read.
 */
class AgentTransfer
{
    public const MIN = 1;
    public const KINDS = ['credit' => 'Credit', 'balance' => 'Commission balance'];

    /** @throws \DomainException with a message safe to show */
    public static function send(Agent $from, Agent $to, float $amount, string $kind, string $by, ?string $note = null): string
    {
        $amount = round($amount, 2);
        if (! array_key_exists($kind, self::KINDS)) {
            throw new \DomainException('Galat transfer type.');
        }
        if ($amount < self::MIN) {
            throw new \DomainException('Kam se kam ₹' . self::MIN . ' transfer kar sakte hain.');
        }
        if ($from->id === $to->id) {
            throw new \DomainException('Ek hi agent ko khud transfer nahi kar sakte.');
        }
        if (! $to->is_active) {
            throw new \DomainException('Jis agent ko bhej rahe hain wo deactivated hai.');
        }

        $ref = 'ATR-' . strtoupper(Str::random(10));

        DB::connection($from->getConnectionName())->transaction(function () use ($from, $to, $amount, $kind) {
            // lock both rows in id order so two opposite transfers can't deadlock
            $rows = Agent::whereIn('id', [$from->id, $to->id])->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $src = $rows[$from->id];
            $dst = $rows[$to->id];

            if ($kind === 'credit') {
                $available = round((float) $src->credit_limit - (float) $src->credit_used, 2);
                if ($amount > $available + 0.00001) {
                    throw new \DomainException('Credit kam hai. ₹' . number_format(max(0, $available), 2) . ' tak hi transfer ho sakta hai.');
                }
                $src->credit_limit = round((float) $src->credit_limit - $amount, 2);
                $dst->credit_limit = round((float) $dst->credit_limit + $amount, 2);
            } else {
                $available = round((float) $src->wallet_balance, 2);
                if ($amount > $available + 0.00001) {
                    throw new \DomainException('Commission balance kam hai. ₹' . number_format(max(0, $available), 2) . ' tak hi transfer ho sakta hai.');
                }
                $src->wallet_balance = round($available - $amount, 2);
                $dst->wallet_balance = round((float) $dst->wallet_balance + $amount, 2);
            }
            $src->save();
            $dst->save();
        });

        AuditLog::record('Agent Transfer', self::KINDS[$kind], json_encode([
            'ref' => $ref, 'kind' => $kind, 'amount' => $amount,
            'from' => $from->id, 'from_name' => $from->name, 'to' => $to->id, 'to_name' => $to->name,
            'by' => $by, 'note' => $note ? Str::limit($note, 120) : null,
        ], JSON_UNESCAPED_UNICODE));

        return $ref;
    }

    /** Recent transfers, optionally only those touching one of $agentIds. */
    public static function history(?array $agentIds = null, int $limit = 30)
    {
        return AuditLog::where('module', 'Agent Transfer')->latest('id')->limit($agentIds ? 500 : $limit)->get()
            ->map(function ($log) {
                $d = json_decode((string) $log->description, true) ?: [];
                $d['at'] = $log->created_at;

                return (object) $d;
            })
            ->filter(fn ($d) => isset($d->from, $d->to) && (! $agentIds || in_array((int) $d->from, $agentIds, true) || in_array((int) $d->to, $agentIds, true)))
            ->take($limit)->values();
    }
}
