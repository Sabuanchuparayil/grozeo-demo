<?php
/**
 * Creates minimal auth-related tables on empty Railway databases.
 * Safe to run repeatedly (CREATE TABLE IF NOT EXISTS / idempotent inserts).
 */

function seedBootstrapMinimalSchema($db)
{
    $ddl = [
        "CREATE TABLE IF NOT EXISTS sys_role (
            RoleId INT NOT NULL AUTO_INCREMENT,
            RoleName VARCHAR(255) NOT NULL DEFAULT '',
            IsEnabled VARCHAR(3) NOT NULL DEFAULT 'Yes',
            PRIMARY KEY (RoleId)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS sys_role_capability (
            RoleId INT NOT NULL,
            SysModOpId INT NOT NULL,
            KEY idx_role (RoleId)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS usr_capability (
            UserId INT NOT NULL,
            SysModOpId INT NOT NULL,
            KEY idx_user (UserId)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS usr_blocked_capability (
            UserId INT NOT NULL,
            SysModOpId INT NOT NULL,
            KEY idx_user (UserId)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS sys_menu (
            MenuId INT NOT NULL AUTO_INCREMENT,
            IsEnabled VARCHAR(3) NOT NULL DEFAULT 'Yes',
            ParentMenuId INT NOT NULL DEFAULT 0,
            PRIMARY KEY (MenuId)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS sys_module (
            ModuleName VARCHAR(255) NOT NULL,
            ModuleTitle VARCHAR(255) NOT NULL DEFAULT '',
            description VARCHAR(255) DEFAULT NULL,
            PRIMARY KEY (ModuleName)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS sys_module_operation (
            OperationId INT NOT NULL AUTO_INCREMENT,
            ModuleName VARCHAR(255) NOT NULL DEFAULT '',
            MenuId INT NOT NULL DEFAULT 0,
            InitFunction VARCHAR(255) DEFAULT NULL,
            Capability VARCHAR(255) DEFAULT NULL,
            Operation VARCHAR(255) DEFAULT NULL,
            title VARCHAR(255) DEFAULT NULL,
            IsEnabled VARCHAR(3) NOT NULL DEFAULT 'Yes',
            PRIMARY KEY (OperationId)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS sys_configuration (
            cfg_Name VARCHAR(255) NOT NULL,
            cfg_Value VARCHAR(255) DEFAULT NULL,
            PRIMARY KEY (cfg_Name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    ];

    foreach ($ddl as $sql) {
        $db->query($sql);
    }

    $roleId = $db->getItemFromDB("SELECT RoleId FROM sys_role WHERE RoleName = 'Super User' LIMIT 1");
    if (empty($roleId)) {
        $db->perform('sys_role', [
            'RoleName' => 'Super User',
            'IsEnabled' => 'Yes',
        ]);
    }

    $configs = [
        'IS_RETALINE_LITE' => '0',
        'IS_MEDICINE_REQUIRED' => '0',
        'DEFAULT_RRP' => '0',
    ];
    foreach ($configs as $name => $value) {
        $exists = $db->getItemFromDB("SELECT cfg_Name FROM sys_configuration WHERE cfg_Name = '" . addslashes($name) . "' LIMIT 1");
        if (empty($exists)) {
            $db->perform('sys_configuration', [
                'cfg_Name' => $name,
                'cfg_Value' => $value,
            ]);
        }
    }
}

function seedResolveRoleId($db)
{
    seedBootstrapMinimalSchema($db);

    $roleId = $db->getItemFromDB("SELECT RoleId FROM sys_role WHERE RoleName = 'Super User' LIMIT 1");
    if (empty($roleId)) {
        $roleId = $db->getItemFromDB('SELECT RoleId FROM sys_role WHERE IsEnabled = "Yes" ORDER BY RoleId ASC LIMIT 1');
    }
    if (empty($roleId)) {
        $db->perform('sys_role', [
            'RoleName' => 'Super User',
            'IsEnabled' => 'Yes',
        ]);
        $roleId = $db->insert_id();
    }

    return (int) $roleId;
}
