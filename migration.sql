CREATE TABLE comptes (
    uuid VARCHAR(36) PRIMARY KEY,
    login VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE ecritures (
    uuid VARCHAR(36) PRIMARY KEY,
    compte_uuid VARCHAR(36),
    label VARCHAR(255) NOT NULL DEFAULT '',
    date DATE,
    type ENUM('C', 'D'),
    amount DOUBLE(14, 2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (compte_uuid) REFERENCES comptes(uuid) ON DELETE CASCADE ON UPDATE RESTRICT
);