<?php

namespace App\Http\Controllers\Certificates;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Services\CertificatePdfService;
use Illuminate\Support\Facades\Auth;

class CertificateDownloadController extends Controller
{
    public function view(Certificate $certificate, CertificatePdfService $service)
    {
        $this->authorizeAccess($certificate);

        return $service->render($certificate, false);
    }

    public function download(Certificate $certificate, CertificatePdfService $service)
    {
        $this->authorizeAccess($certificate);

        return $service->render($certificate, true);
    }

    private function authorizeAccess(Certificate $certificate): void
    {
        $user = Auth::user();

        if (!$user) {
            abort(403);
        }

        if ((int) $certificate->user_id !== (int) $user->id && !$user->hasAnyRole(['admin'])) {
            abort(403);
        }
    }
}
