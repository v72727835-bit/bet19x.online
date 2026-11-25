<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Moves money between an agent (master / distributor / retailer) and a player.
 *
 * Agent -> player: paid from the agent's unused credit (its credit limit goes
 *   down) or its commission balance; the player gets it as deposit balance
 *   (playable; withdrawable only after it is won, like every other transfer).
 * Player -> agent: paid from the player's deposit balance first, then winnings;
 *   the agent gets it as credit (its credit limit goes up) or commission balance.
 *
 * No network restriction: any active agent and any unblocked player can be on
 * either side. Every transfer writes a wallet transaction for the player and an
 * audit log entry (module "Agent User Transfer") that the history tables read.
 */
class AgentUserTransfer
{
    public const MODULE = 'Agent User Transfer';
    public const MIN = WalletTransfer::MIN;
    public const MAX = 1000000;

    /** @throws \DomainException with a message safe to show */
    public static function agentToUser(Agent $agent, User $user, float $amount, string $kind, string $by, ?string $note = null): string
    {
        $amount = self::check($amount, $kind);
        if ($user->is_blocked) {
            throw new \DomainException('Jis user ko bhej rahe hain uska account block hai.');
        }

        $ref = 'AUT-' . strtoupper(Str::random(10));

        DB::connection($agent->getConnectionName())->transaction(function () use ($agent, $user, $amount, $kind, $by, $note, $ref) {
            Wallet::firstOrCreate(['user_id' => $user->id], ['deposit_balance' => 0, 'winnings_balance' => 0, 'bonus_balance' => 0]);
            // always lock the agent first, then the wallet, so opposite transfers can't deadlock
            $src = Agent::lockForUpdate()->find($agent->id);
            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();

            if ($kind === 'credit') {
                $available = round((float) $src->credit_limit - (float) $src->credit_used, 2);
                if ($amount > $available + 0.00001) {
                    throw new \DomainException('Credit kam hai. ₹' . number_format(max(0, $available), 2) . ' tak hi transfer ho sakta hai.');
                }
                $src->credit_limit = round((float) $src->credit_limit - $amount, 2);
            } else {
                $available = round((float) $src->wallet_balance, 2);
                if ($amount > $available + 0.00001) {
                    throw new \DomainException('Commission balance kam hai. ₹' . number_format(max(0, $available), 2) . ' tak hi transfer ho sakta hai.');
                }
                $src->wallet_balance = round($available - $amount, 2);
            }
            $src->save();

            $wallet->deposit_balance = round((float) $wallet->deposit_balance + $amount, 2);
            $wallet->save();

            WalletTransaction::create([
                'wallet_id' => $wallet->id, 'type' => 'transfer_in', 'amount' => $amount,
                'description' => "Transfer from agent {$src->name} ({$src->referralCode()})" . self::suffix($by, $note),
                'reference_id' => $ref,
            ]);
        });

        self::log($ref, 'agent_to_user', $kind, $amount, $agent, $user, $by, $note);

        return $ref;
    }

    /** @throws \DomainException with a message safe to show */
    public static function userToAgent(User $user, Agent $agent, float $amount, string $kind, string $by, ?string $note = null): string
    {
        $amount = self::check($amount, $kind);
        if (! $agent->is_active) {
            throw new \DomainException('Jis agent ko bhej rahe hain wo deactivated hai.');
        }
        if ($user->is_blocked) {
            throw new \DomainException('Is user ka account block hai, transfer nahi ho sakta.');
        }

        $ref = 'UAT-' . strtoupper(Str::random(10));

        DB::connection($agent->getConnectionName())->transaction(function () use ($agent, $user, $amount, $kind, $by, $note, $ref) {
            Wallet::firstOrCreate(['user_id' => $user->id], ['deposit_balance' => 0, 'winnings_balance' => 0, 'bonus_balance' => 0]);
            $dst = Agent::lockForUpdate()->find($agent->id);
            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();

            if (! $dst || ! $dst->is_active) {
                throw new \DomainException('Jis agent ko bhej rahe hain wo deactivated hai.');
            }

            $dep = (float) $wallet->deposit_balance;
            $win = (float) $wallet->winnings_balance;
            if ($amount > round($dep + $win, 2) + 0.00001) {
                throw new \DomainException('Balance kam hai. ₹' . number_format($dep + $win, 2) . ' tak hi transfer ho sakta hai.');
            }
            $fromDep = round(min($dep, $amount), 2);
            $fromWin = round($amount - $fromDep, 2);
            $wallet->deposit_balance = round($dep - $fromDep, 2);
            $wallet->winnings_balance = round($win - $fromWin, 2);
            $wallet->save();

            if ($kind === 'credit') {
                $dst->credit_limit = round((float) $dst->credit_limit + $amount, 2);
            } else {
                $dst->wallet_balance = round((float) $dst->wallet_balance + $amount, 2);
            }
            $dst->save();

            WalletTransaction::create([
                'wallet_id' => $wallet->id, 'type' => 'transfer_out', 'amount' => $amount,
                'description' => "Transfer to agent {$dst->name} ({$dst->referralCode()})" . self::suffix($by, $note),
                'reference_id' => $ref,
            ]);
        });

        self::log($ref, 'user_to_agent', $kind, $amount, $agent, $user, $by, $note);

        return $ref;
    }

    /**
     * Recent agent <-> player transfers, optionally only those of one agent or one player.
     *
     * @return \Illuminate\Support\Collection<int, object>
     */
    public static function history(?int $agentId = null, ?int $userId = null, int $limit = 30)
    {
        $filtered = $agentId !== null || $userId !== null;

        return AuditLog::where('module', self::MODULE)->latest('id')->limit($filtered ? 1000 : $limit)->get()
            ->map(function ($log) {
                $d = json_decode((string) $log->description, true) ?: [];
                $d['at'] = $log->created_at;

                return (object) $d;
            })
            ->filter(fn ($d) => isset($d->agent, $d->user, $d->dir)
                && ($agentId === null || (int) $d->agent === $agentId)
                && ($userId === null || (int) $d->user === $userId))
            ->take($limit)->values();
    }

    private static function check(float $amount, string $kind): float
    {
        $amount = round($amount, 2);
        if (! array_key_exists($kind, AgentTransfer::KINDS)) {
            throw new \DomainException('Galat transfer type.');
        }
        if ($amount < self::MIN) {
            throw new \DomainException('Kam se kam ₹' . self::MIN . ' transfer kar sakte hain.');
        }
        if ($amount > self::MAX) {
            throw new \DomainException('Ek baar mein ₹' . number_format(self::MAX) . ' se zyada transfer nahi ho sakta.');
        }
        if (! WalletTransfer::ready()) {
            throw new \DomainException('Transfer abhi chalu nahi hai: server par "php artisan migrate" chalana baaki hai.');
        }

        return $amount;
    }

    private static function suffix(string $by, ?string $note): string
    {
        return ($by !== 'player' && ! str_starts_with($by, 'agent ') ? " (by {$by})" : '') . ($note ? ' — ' . Str::limit($note, 120) : '');
    }

    private static function log(string $ref, string $dir, string $kind, float $amount, Agent $agent, User $user, string $by, ?string $note): void
    {
        AuditLog::record(self::MODULE, $dir === 'agent_to_user' ? 'Agent to user' : 'User to agent', json_encode([
            'ref' => $ref, 'dir' => $dir, 'kind' => $kind, 'amount' => $amount,
            'agent' => $agent->id, 'agent_name' => $agent->name, 'agent_code' => $agent->referralCode(),
            'user' => $user->id, 'user_name' => $user->name,
            'by' => $by, 'note' => $note ? Str::limit($note, 120) : null,
        ], JSON_UNESCAPED_UNICODE));
    }
}
