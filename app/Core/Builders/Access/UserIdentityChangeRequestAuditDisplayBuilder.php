<?php

namespace App\Core\Builders\Access;

use App\Core\Builders\Contracts\Access\UserIdentityChangeRequestAuditDisplayBuilderInterface;
use App\Core\Models\User;
use App\Core\Models\UserIdentityChangeRequest;

class UserIdentityChangeRequestAuditDisplayBuilder implements UserIdentityChangeRequestAuditDisplayBuilderInterface
{
    public function buildRequestedDisplay(User $user, UserIdentityChangeRequest $request): array
    {
        return [
            'summary' => 'Identity change requested for ' . $this->userDisplayName($user),
            'subject_label' => $this->userDisplayName($user),
            'sections' => [
                [
                    'title' => 'Requested Identity Changes',
                    'items' => $this->comparisonItems($request),
                ],
                [
                    'title' => 'Workflow State',
                    'items' => [
                        [
                            'label' => 'Request Status',
                            'before' => 'No Pending Request',
                            'after' => 'Pending Approval',
                        ],
                    ],
                ],
            ],
            'request_details' => array_filter([
                'Requester Username' => $user->username ?: null,
                'Requester Email' => $user->email ?: null,
                'Submitted At' => $request->created_at?->format('M d, Y h:i A'),
                'Reason' => $request->reason ?: null,
            ]),
        ];
    }

    public function buildApprovedDisplay(User $user, UserIdentityChangeRequest $request, User $reviewer): array
    {
        return [
            'summary' => 'Identity change approved for ' . $this->userDisplayName($user),
            'subject_label' => $this->userDisplayName($user),
            'sections' => [
                [
                    'title' => 'Approved Identity Changes',
                    'items' => $this->comparisonItems($request),
                ],
                [
                    'title' => 'Workflow State',
                    'items' => [
                        [
                            'label' => 'Request Status',
                            'before' => 'Pending Approval',
                            'after' => 'Approved',
                        ],
                    ],
                ],
            ],
            'request_details' => array_filter([
                'Reviewed By' => $this->userDisplayName($reviewer),
                'Reviewed At' => $request->reviewed_at?->format('M d, Y h:i A'),
                'Review Notes' => $request->review_notes ?: null,
            ]),
        ];
    }

    public function buildRejectedDisplay(User $user, UserIdentityChangeRequest $request, User $reviewer): array
    {
        return [
            'summary' => 'Identity change rejected for ' . $this->userDisplayName($user),
            'subject_label' => $this->userDisplayName($user),
            'sections' => [
                [
                    'title' => 'Rejected Identity Changes',
                    'items' => $this->comparisonItems($request),
                ],
                [
                    'title' => 'Workflow State',
                    'items' => [
                        [
                            'label' => 'Request Status',
                            'before' => 'Pending Approval',
                            'after' => 'Rejected',
                        ],
                    ],
                ],
            ],
            'request_details' => array_filter([
                'Reviewed By' => $this->userDisplayName($reviewer),
                'Reviewed At' => $request->reviewed_at?->format('M d, Y h:i A'),
                'Review Notes' => $request->review_notes ?: null,
            ]),
        ];
    }

    private function comparisonItems(UserIdentityChangeRequest $request): array
    {
        return [
            [
                'label' => 'First Name',
                'before' => $request->current_first_name ?: 'None',
                'after' => $request->requested_first_name ?: 'None',
            ],
            [
                'label' => 'Middle Name',
                'before' => $request->current_middle_name ?: 'None',
                'after' => $request->requested_middle_name ?: 'None',
            ],
            [
                'label' => 'Last Name',
                'before' => $request->current_last_name ?: 'None',
                'after' => $request->requested_last_name ?: 'None',
            ],
            [
                'label' => 'Name Extension',
                'before' => $request->current_name_extension ?: 'None',
                'after' => $request->requested_name_extension ?: 'None',
            ],
        ];
    }

    private function userDisplayName(User $user): string
    {
        $profileName = trim((string) ($user->profile?->full_name ?? ''));

        if ($profileName !== '') {
            return $profileName;
        }

        return (string) ($user->username ?: $user->email ?: 'User');
    }
}
