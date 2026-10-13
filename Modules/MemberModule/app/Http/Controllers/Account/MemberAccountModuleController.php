<?php

namespace Modules\MemberModule\app\Http\Controllers\Account;

use Illuminate\Routing\Controller;
use Modules\MemberModule\app\Http\Controllers\Concerns\MemberActions;

// logged in account: its members (MemberActions) + delete (the account only: a member is deleted for good)
class MemberAccountModuleController extends Controller
{
    use MemberActions;

    protected function area(): string
    {
        return 'account';
    }

    public function destroy($id)
    {
        $this->memberService->deleteOne($this->accountId(), $id);

        return response()->json([
            'message' => 'The member has been deleted successfully.',
            'row' => null,
            'redirect' => route('account.members.index'),
        ]);
    }
}
