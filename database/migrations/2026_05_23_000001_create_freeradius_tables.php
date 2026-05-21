<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("CREATE TABLE IF NOT EXISTS radacct (
            radacctid BIGSERIAL PRIMARY KEY,
            acctsessionid VARCHAR(64) NOT NULL DEFAULT '',
            acctuniqueid VARCHAR(32) NOT NULL UNIQUE,
            username VARCHAR(64) NOT NULL DEFAULT '',
            realm VARCHAR(64),
            nasipaddress INET NOT NULL,
            nasportid VARCHAR(15),
            nasporttype VARCHAR(32),
            acctstarttime TIMESTAMPTZ,
            acctupdatetime TIMESTAMPTZ,
            acctstoptime TIMESTAMPTZ,
            acctinterval BIGINT,
            acctsessiontime BIGINT,
            acctauthentic VARCHAR(32),
            connectinfo_start VARCHAR(128),
            connectinfo_stop VARCHAR(128),
            acctinputoctets BIGINT,
            acctoutputoctets BIGINT,
            calledstationid VARCHAR(50),
            callingstationid VARCHAR(50),
            acctterminatecause VARCHAR(32),
            servicetype VARCHAR(32),
            framedprotocol VARCHAR(32),
            framedipaddress INET,
            framedipv6address INET,
            framedipv6prefix INET,
            framedinterfaceid VARCHAR(44),
            delegatedipv6prefix INET
        )");
        DB::statement("CREATE INDEX IF NOT EXISTS radacct_active_idx ON radacct (acctuniqueid) WHERE acctstoptime IS NULL");
        DB::statement("CREATE INDEX IF NOT EXISTS radacct_username_idx ON radacct (username)");
        DB::statement("CREATE INDEX IF NOT EXISTS radacct_starttime_idx ON radacct (acctstarttime)");

        DB::statement("CREATE TABLE IF NOT EXISTS radpostauth (
            id BIGSERIAL PRIMARY KEY,
            username TEXT NOT NULL,
            pass TEXT,
            reply VARCHAR(32),
            authdate TIMESTAMPTZ NOT NULL DEFAULT now()
        )");
        DB::statement("CREATE INDEX IF NOT EXISTS radpostauth_user_idx ON radpostauth (username)");
        DB::statement("CREATE INDEX IF NOT EXISTS radpostauth_date_idx ON radpostauth (authdate)");

        DB::statement("CREATE TABLE IF NOT EXISTS nas (
            id BIGSERIAL PRIMARY KEY,
            nasname VARCHAR(128) UNIQUE NOT NULL,
            shortname VARCHAR(32),
            type VARCHAR(30) DEFAULT 'other',
            ports INTEGER,
            secret VARCHAR(60) DEFAULT 'secret' NOT NULL,
            server VARCHAR(64),
            community VARCHAR(50),
            description VARCHAR(200) DEFAULT 'RADIUS Client'
        )");

        // FreeRADIUS may probe these even if we don't use them — create stubs
        DB::statement("CREATE TABLE IF NOT EXISTS radcheck (id BIGSERIAL PRIMARY KEY, username VARCHAR(64) NOT NULL DEFAULT '', attribute VARCHAR(64) NOT NULL DEFAULT '', op CHAR(2) NOT NULL DEFAULT '==', value VARCHAR(253) NOT NULL DEFAULT '')");
        DB::statement("CREATE TABLE IF NOT EXISTS radreply (id BIGSERIAL PRIMARY KEY, username VARCHAR(64) NOT NULL DEFAULT '', attribute VARCHAR(64) NOT NULL DEFAULT '', op CHAR(2) NOT NULL DEFAULT '=', value VARCHAR(253) NOT NULL DEFAULT '')");
        DB::statement("CREATE TABLE IF NOT EXISTS radusergroup (id BIGSERIAL PRIMARY KEY, username VARCHAR(64) NOT NULL DEFAULT '', groupname VARCHAR(64) NOT NULL DEFAULT '', priority INTEGER NOT NULL DEFAULT 0)");
        DB::statement("CREATE TABLE IF NOT EXISTS radgroupcheck (id BIGSERIAL PRIMARY KEY, groupname VARCHAR(64) NOT NULL DEFAULT '', attribute VARCHAR(64) NOT NULL DEFAULT '', op CHAR(2) NOT NULL DEFAULT '==', value VARCHAR(253) NOT NULL DEFAULT '')");
        DB::statement("CREATE TABLE IF NOT EXISTS radgroupreply (id BIGSERIAL PRIMARY KEY, groupname VARCHAR(64) NOT NULL DEFAULT '', attribute VARCHAR(64) NOT NULL DEFAULT '', op CHAR(2) NOT NULL DEFAULT '=', value VARCHAR(253) NOT NULL DEFAULT '')");
    }

    public function down(): void
    {
        DB::statement("DROP TABLE IF EXISTS radacct, radpostauth, nas, radcheck, radreply, radusergroup, radgroupcheck, radgroupreply CASCADE");
    }
};
