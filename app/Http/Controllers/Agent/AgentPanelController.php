<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AstechBetLog;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Agent panel. Every query is limited to the signed-in agent and the agents
 * below it, so a sub-agent sees only its own players and a master agent sees
 * its whole tree.
 */
class AgentPanelController extends Controller
{
    public function dashboard(Request $request)
    {
        /** @var Agent $agent */
        $agent = $request->user('agent');
        $scope = $agent->scopeIds();

        $subAgents = Agent::whereIn('id', $agent->descendantIds())->with('parent')->withCount('users')->orderBy('role')->orderBy('name')->get();

        $usersQuery = User::whereIn('agent_id', $scope)->with('wallet');
        if ($q = trim((string) $request->get('q'))) {
            $usersQuery->where(fn ($w) => $w->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%"));
        }
        $users = $usersQuery->latest()->paginate(25)->withQueryString();

        $allUserIds = User::whereIn('agent_id', $scope)->pluck('id')->map(fn ($id) => (string) $id)->all();
        $totals = AstechBetLog::whereIn('user_id', $allUserIds ?: ['-'])->where('rolled_back', false)
            ->selectRaw('COUNT(*) as rounds, COALESCE(SUM(bet),0) as bet, COALESCE(SUM(win),0) as win')->first();
        $perUser = AstechBetLog::whereIn('user_id', $users->pluck('id')->map(fn ($id) => (string) $id)->all() ?: ['-'])
            ->where('rolled_back', false)->groupBy('user_id')
            ->selectRaw('user_id, COALESCE(SUM(bet),0) as bet, COALESCE(SUM(win),0) as win')->get()->keyBy('user_id');

        $ggr = (float) $totals->bet - (float) $totals->win;
        $commission = max($ggr, 0) * ((float) $agent->commission_rate / 100);

        return view('agent.dashboard', [
            'agent' => $agent,
            'subAgents' => $subAgents,
            'users' => $users,
            'playerCount' => count($allUserIds),
            'totals' => $totals,
            'perUser' => $perUser,
            'ggr' => $ggr,
            'commission' => $commission,
        ]);
    }
}
