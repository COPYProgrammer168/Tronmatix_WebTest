<?php

// app/Services/FirebaseAuthService.php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Kreait\Firebase\JWT\IdTokenVerifier;
use Throwable;

/**
 * Verifies Firebase ID tokens issued by the Firebase JS SDK (signInWithPhoneNumber).
 *
 * Uses IdTokenVerifier directly — it verifies the RS256 signature against
 * Google's public JWKS keys using ONLY the project id. No service-account
 * file is required (unlike Factory::createAuth(), which needs credentials).
 */
class FirebaseAuthService
{
    private ?IdTokenVerifier $verifier = null;

    private function verifier(): IdTokenVerifier
    {
        if ($this->verifier) {
            return $this->verifier;
        }

        $projectId = config('firebase.project_id');

        if (! $projectId) {
            throw new \RuntimeException('FIREBASE_PROJECT_ID is not configured.');
        }

        return $this->verifier = IdTokenVerifier::createWithProjectId($projectId);
    }

    /**
     * Verify a Firebase ID token and return its claims, or null if invalid.
     *
     * @return array<string,mixed>|null  claims (incl. phone_number) or null
     */
    public function verifyIdToken(string $idToken): ?array
    {
        if (! $idToken) {
            return null;
        }

        try {
            $token = $this->verifier()->verifyIdToken($idToken);

            return $token->claims()->all();
        } catch (Throwable $e) {
            Log::channel('security')->notice('Firebase ID token verification failed: ' . $e->getMessage());

            return null;
        }
    }
}
