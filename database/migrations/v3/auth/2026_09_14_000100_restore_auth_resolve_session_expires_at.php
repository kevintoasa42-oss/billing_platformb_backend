<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Restore auth.resolve_session() to include expires_at in its return signature.
 *
 * The original migration (2026_09_13_000000_create_v3_auth_schema) defined the
 * function with expires_at, but the live function was modified out-of-band and
 * lost the column. This re-applies the original definition so the PHP repository
 * can SELECT expires_at again.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            DROP FUNCTION IF EXISTS auth.resolve_session(text);

            CREATE FUNCTION auth.resolve_session(token text)
            RETURNS TABLE(tenant_id uuid, user_id uuid, capabilities text[], platform_admin boolean, expires_at timestamptz)
            LANGUAGE sql
            SECURITY DEFINER
            SET search_path = pg_catalog
            AS $$
                SELECT s.tenant_id, s.user_id, m.capabilities, u.platform_admin, s.expires_at
                FROM auth.sessions s
                JOIN auth.tenant_memberships m
                    ON m.tenant_id = s.tenant_id
                    AND m.id = s.membership_id
                    AND m.user_id = s.user_id
                JOIN auth.users u ON u.id = s.user_id
                WHERE s.token_hash = token
                    AND s.expires_at > now()
                    AND m.active
                    AND u.active
                    AND s.authorization_version = m.authorization_version
            $$;

            REVOKE ALL ON FUNCTION auth.resolve_session(text) FROM PUBLIC;
            SQL);
    }

    public function down(): void
    {
        DB::connection('master_v3')->unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION auth.resolve_session(token text)
            RETURNS TABLE(tenant_id uuid, user_id uuid, capabilities text[], platform_admin boolean)
            LANGUAGE sql
            SECURITY DEFINER
            SET search_path = pg_catalog
            AS $$
                SELECT s.tenant_id, s.user_id, m.capabilities, u.platform_admin
                FROM auth.sessions s
                JOIN auth.tenant_memberships m
                    ON m.tenant_id = s.tenant_id
                    AND m.id = s.membership_id
                    AND m.user_id = s.user_id
                JOIN auth.users u ON u.id = s.user_id
                WHERE s.token_hash = token
                    AND s.expires_at > now()
                    AND m.active
                    AND u.active
                    AND s.authorization_version = m.authorization_version
            $$;
            SQL);
    }
};
