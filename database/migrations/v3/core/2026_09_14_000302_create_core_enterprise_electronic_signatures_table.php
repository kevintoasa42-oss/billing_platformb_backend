<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS core.enterprise_electronic_signatures (
                tenant_id uuid NOT NULL,
                id uuid NOT NULL DEFAULT gen_random_uuid() PRIMARY KEY,
                company_id uuid NOT NULL,
                has_electronic_signature boolean NOT NULL DEFAULT false,
                sri_environment_id bigint NULL,
                sri_mode text NOT NULL DEFAULT 'celcer',
                file_type text NULL,
                file_name text NULL,
                password text NULL,
                expiration_date date NULL,
                max_invoices_per_month integer NOT NULL DEFAULT 0,
                key_backend text NOT NULL DEFAULT 'legacy_p12',
                key_reference text NULL,
                key_version integer NULL,
                certificate_pem text NULL,
                certificate_chain jsonb NULL,
                fingerprint_sha256 text NULL,
                certificate_serial text NULL,
                certificate_subject text NULL,
                certificate_issuer text NULL,
                certificate_ruc text NULL,
                valid_from timestamptz NULL,
                valid_until timestamptz NULL,
                key_algorithm text NULL,
                key_bits integer NULL,
                validation_status text NOT NULL DEFAULT 'pending',
                migration_status text NOT NULL DEFAULT 'legacy',
                credential_version integer NOT NULL DEFAULT 1,
                custody_verified_at timestamptz NULL,
                migrated_at timestamptz NULL,
                custody_last_signed_at timestamptz NULL,
                legacy_last_used_at timestamptz NULL,
                custody_celcer_authorized_at timestamptz NULL,
                custody_celcer_evidence_sha256 text NULL,
                legacy_retired_at timestamptz NULL,
                created_at timestamptz NOT NULL DEFAULT now(),
                updated_at timestamptz NOT NULL DEFAULT now(),
                UNIQUE (tenant_id, company_id)
            );
        SQL);

        DB::statement('ALTER TABLE core.enterprise_electronic_signatures ENABLE ROW LEVEL SECURITY');
        DB::statement(<<<'SQL'
            CREATE POLICY tenant_isolation ON core.enterprise_electronic_signatures
            USING (tenant_id = auth.tenant_id())
            WITH CHECK (tenant_id = auth.tenant_id());
        SQL);
        DB::statement('ALTER TABLE core.enterprise_electronic_signatures FORCE ROW LEVEL SECURITY');
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS core.enterprise_electronic_signatures');
    }
};
