-- ============================================================
-- Debt & Installment Management System (Multi-Tenant)
-- ============================================================

-- المتاجر (Tenants)
CREATE TABLE IF NOT EXISTS tenants (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    name        TEXT    NOT NULL,
    phone       TEXT,
    address     TEXT,
    active      INTEGER NOT NULL DEFAULT 1,
    created_at  TEXT    NOT NULL DEFAULT (datetime('now','localtime'))
);

-- المستخدمون
-- role: super_admin (tenant_id NULL) | admin | employee
CREATE TABLE IF NOT EXISTS users (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    tenant_id   INTEGER,                                    -- NULL لـ super_admin
    username    TEXT    NOT NULL UNIQUE,
    password    TEXT    NOT NULL,
    full_name   TEXT    NOT NULL,
    role        TEXT    NOT NULL DEFAULT 'employee',        -- super_admin | admin | employee
    active      INTEGER NOT NULL DEFAULT 1,
    last_login  TEXT,
    created_at  TEXT    NOT NULL DEFAULT (datetime('now','localtime')),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS idx_users_tenant ON users(tenant_id);

CREATE TABLE IF NOT EXISTS customers (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    tenant_id   INTEGER NOT NULL,
    name        TEXT    NOT NULL,
    phone       TEXT,
    address     TEXT,
    notes       TEXT,
    created_by  INTEGER,
    created_at  TEXT    NOT NULL DEFAULT (datetime('now','localtime')),
    FOREIGN KEY (tenant_id)  REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)   ON DELETE SET NULL
);
CREATE INDEX IF NOT EXISTS idx_customers_tenant ON customers(tenant_id);
CREATE INDEX IF NOT EXISTS idx_customers_name   ON customers(name);
CREATE INDEX IF NOT EXISTS idx_customers_phone  ON customers(phone);

CREATE TABLE IF NOT EXISTS debts (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    tenant_id       INTEGER NOT NULL,
    customer_id     INTEGER NOT NULL,
    amount          REAL    NOT NULL CHECK (amount > 0),
    debt_date       TEXT    NOT NULL,
    due_date        TEXT,
    payment_type    TEXT    NOT NULL DEFAULT 'full',
    installment_freq TEXT,
    installment_count INTEGER DEFAULT 0,
    description     TEXT,
    status          TEXT    NOT NULL DEFAULT 'active',
    created_by      INTEGER,
    created_at      TEXT    NOT NULL DEFAULT (datetime('now','localtime')),
    FOREIGN KEY (tenant_id)   REFERENCES tenants(id)   ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by)  REFERENCES users(id)     ON DELETE SET NULL
);
CREATE INDEX IF NOT EXISTS idx_debts_tenant   ON debts(tenant_id);
CREATE INDEX IF NOT EXISTS idx_debts_customer ON debts(customer_id);
CREATE INDEX IF NOT EXISTS idx_debts_status   ON debts(status);

CREATE TABLE IF NOT EXISTS installments (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    tenant_id   INTEGER NOT NULL,
    debt_id     INTEGER NOT NULL,
    seq         INTEGER NOT NULL,
    amount      REAL    NOT NULL,
    due_date    TEXT    NOT NULL,
    paid_amount REAL    NOT NULL DEFAULT 0,
    status      TEXT    NOT NULL DEFAULT 'pending',
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (debt_id)   REFERENCES debts(id)   ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS idx_inst_tenant ON installments(tenant_id);
CREATE INDEX IF NOT EXISTS idx_inst_debt   ON installments(debt_id);
CREATE INDEX IF NOT EXISTS idx_inst_due    ON installments(due_date);

CREATE TABLE IF NOT EXISTS payments (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    tenant_id   INTEGER NOT NULL,
    debt_id     INTEGER NOT NULL,
    installment_id INTEGER,
    amount      REAL    NOT NULL CHECK (amount > 0),
    paid_at     TEXT    NOT NULL DEFAULT (datetime('now','localtime')),
    method      TEXT    DEFAULT 'cash',
    notes       TEXT,
    received_by INTEGER,
    created_at  TEXT    NOT NULL DEFAULT (datetime('now','localtime')),
    FOREIGN KEY (tenant_id)      REFERENCES tenants(id)      ON DELETE CASCADE,
    FOREIGN KEY (debt_id)        REFERENCES debts(id)        ON DELETE CASCADE,
    FOREIGN KEY (installment_id) REFERENCES installments(id) ON DELETE SET NULL,
    FOREIGN KEY (received_by)    REFERENCES users(id)        ON DELETE SET NULL
);
CREATE INDEX IF NOT EXISTS idx_pay_tenant ON payments(tenant_id);
CREATE INDEX IF NOT EXISTS idx_pay_debt   ON payments(debt_id);
CREATE INDEX IF NOT EXISTS idx_pay_date   ON payments(paid_at);

-- إعدادات لكل متجر
CREATE TABLE IF NOT EXISTS settings (
    tenant_id INTEGER NOT NULL,
    key       TEXT NOT NULL,
    value     TEXT,
    PRIMARY KEY (tenant_id, key),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);
