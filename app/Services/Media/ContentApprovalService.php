<?php

namespace App\Services\Media;

use App\Events\Media\ContentApproved;
use App\Events\Media\ContentRejected;
use App\Models\ContentApproval;
use App\Models\MediaActivityLog;
use App\Models\MediaLibrary;

class ContentApprovalService
{
    public function request(MediaLibrary $media, int $requestedBy): ContentApproval
    {
        $media->update(['approval_status' => 'pending']);

        $approval = ContentApproval::create([
            'media_file_id' => $media->id,
            'requested_by'  => $requestedBy,
            'status'        => 'pending',
        ]);

        MediaActivityLog::record($media, 'approval_requested', 'success');

        return $approval;
    }

    public function approve(ContentApproval $approval, int $approverId, ?string $comments = null): ContentApproval
    {
        $approval->update([
            'approved_by' => $approverId,
            'status'      => 'approved',
            'comments'    => $comments,
            'approved_at' => now(),
        ]);

        $approval->mediaFile->update(['approval_status' => 'approved']);

        MediaActivityLog::record($approval->mediaFile, 'approved', 'success');
        event(new ContentApproved($approval->mediaFile));

        return $approval->fresh(['requester', 'approver']);
    }

    public function reject(ContentApproval $approval, int $rejectorId, string $comments): ContentApproval
    {
        $approval->update([
            'approved_by' => $rejectorId,
            'status'      => 'rejected',
            'comments'    => $comments,
        ]);

        $approval->mediaFile->update(['approval_status' => 'rejected']);

        MediaActivityLog::record($approval->mediaFile, 'rejected', 'error', [], $comments);
        event(new ContentRejected($approval->mediaFile));

        return $approval->fresh(['requester', 'approver']);
    }

    public function pendingForTenant(int $tenantId): \Illuminate\Database\Eloquent\Collection
    {
        return ContentApproval::with(['mediaFile', 'requester'])
            ->whereHas('mediaFile', fn ($q) => $q->where('tenant_id', $tenantId))
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->get();
    }
}
