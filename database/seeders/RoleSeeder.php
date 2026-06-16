<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $role1 = Role::firstOrCreate(['name' => 'Admin']);
        $role2 = Role::firstOrCreate(['name' => 'Producer']);
        $role3 = Role::firstOrCreate(['name' => 'Distributor']);

        //NUEVOS PERMISOS---------------------------------------------
        Permission::firstOrCreate(['name' => 'menu-virtual-class', 'description' => 'Course services', 'section' => 'true'])->syncRoles([$role1, $role2, $role3]); //NO MODIFICAR
        Permission::firstOrCreate(['name' => 'requests', 'description' => 'requests', 'section' => 'true'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'reports', 'description' => 'reports', 'section' => 'true'])->syncRoles([$role1, $role2, $role3]);
        Permission::firstOrCreate(['name' => 'config', 'description' => 'config', 'section' => 'true'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'menu-dashboard', 'description' => 'Dashboard', 'section' => 'true'])->syncRoles([$role1, $role2, $role3]); //NO MODIFICAR
        Permission::firstOrCreate(['name' => 'marketing', 'description' => 'Marketing', 'section' => 'true'])->syncRoles([$role1, $role2, $role3]); // NUEVA LÍNEA
        Permission::firstOrCreate(['name' => 'marketing.calendar', 'description' => 'Marketing Calendar', 'module' => 'marketing'])->syncRoles([$role1, $role2, $role3]);
        Permission::firstOrCreate(['name' => 'marketing.tools', 'description' => 'Marketing Tools', 'module' => 'marketing'])->syncRoles([$role1, $role2, $role3]); // Solo Admin y Producer
        Permission::firstOrCreate(['name' => 'marketing.create', 'description' => 'Create Tools', 'module' => 'marketing'])->syncRoles([$role1, $role2]); // Solo Admin y Producer
        Permission::firstOrCreate(['name' => 'marketing.report', 'description' => 'Reports', 'module' => 'marketing'])->syncRoles([$role1, $role2, $role3]);
        Permission::firstOrCreate(['name' => 'marketing.pages', 'description' => 'Pages', 'module' => 'marketing'])->syncRoles([$role1, $role2, $role3]);
        Permission::firstOrCreate(['name' => 'dashboard-ecommerce', 'description' => 'eCommerce', 'section' => 'true'])->syncRoles([$role1, $role2, $role3]); //NO MODIFICAR
        Permission::firstOrCreate(['name' => 'binary-branch', 'description' => 'Red-Binary', 'section' => 'true'])->syncRoles([$role1, $role2, $role3]); //NO MODIFICAR
        Permission::firstOrCreate(['name' => 'organization', 'description' => 'organization', 'section' => 'true'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'configuration-certificates', 'description' => 'Configuration certificates', 'module' => 'config'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'role.index', 'description' => 'Roles and Permissions', 'module' => 'config'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'user-levels-index', 'description' => 'User Levels', 'module' => 'config'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'action-list-role', 'description' => 'List roles', 'action' => 'role.index'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'action-edit-role', 'description' => 'Edit roles', 'action' => 'role.index'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'action-remove-role', 'description' => 'Remove roles', 'action' => 'role.index'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'action-list-permissions', 'description' => 'List permissions', 'action' => 'role.index'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'action-add-remove-permission', 'description' => 'Add and remove permissions', 'action' => 'role.index'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'category-index', 'description' => 'categories list', 'module' => 'config'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'category-create', 'description' => 'category create', 'module' => 'category-index'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'category-edit', 'description' => 'category edit', 'module' => 'category-index'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'courses.verification', 'description' => 'Verification Courses', 'module' => 'requests'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'classroom-certificates', 'description' => 'Add Certificate', 'module' => 'menu-virtual-class'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'courses.create', 'description' => 'Create course', 'action' => 'courses.index'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'courses.edit', 'description' => 'Edit Courses', 'action' => 'courses.index'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'courses.index', 'description' => 'List Courses', 'module' => 'menu-virtual-class'])->syncRoles([$role1, $role2, $role3]);
        Permission::firstOrCreate(['name' => 'chatgpt.index', 'description' => 'List Gpt', 'module' => 'menu-virtual-class'])->syncRoles([$role1, $role2, $role3]);
        Permission::firstOrCreate(['name' => 'marketplace.toggle', 'description' => 'Marketplace Course Toggle', 'module' => 'menu-virtual-class'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'marketing.index', 'description' => 'Marketing pages creation', 'module' => 'menu-virtual-class'])->syncRoles([$role1, $role2, $role3]);
        Permission::firstOrCreate(['name' => 'exam-create', 'description' => 'List Exam', 'module' => 'menu-virtual-class'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'exams.rate', 'description' => 'Rate Exam', 'module' => 'menu-virtual-class'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'courses.subs', 'description' => 'Subscriptor Courses', 'module' => 'menu-virtual-class'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'withdrawal_funds', 'description' => 'List Payments', 'module' => 'requests'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'report-wallets', 'description' => 'User Funds', 'module' => 'reports'])->syncRoles([$role1, $role2, $role3]);
        Permission::firstOrCreate(['name' => 'report-nmywallet', 'description' => 'My Wallet', 'module' => 'reports'])->syncRoles([$role1, $role2, $role3]);
        Permission::firstOrCreate(['name' => 'report-purchase', 'description' => 'My Purchase', 'module' => 'reports'])->syncRoles([$role1, $role2, $role3]);
        Permission::firstOrCreate(['name' => 'report-sales', 'description' => 'My Sales', 'module' => 'reports'])->syncRoles([$role1, $role2, $role3]);
        Permission::firstOrCreate(['name' => 'report-startingBonus', 'description' => 'Starting Bonus List', 'module' => 'reports'])->syncRoles([$role1, $role2, $role3]);
        Permission::firstOrCreate(['name' => 'payment', 'description' => 'Payment', 'module' => 'reports'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'report-growthBonus', 'description' => 'Growth Bonus List', 'module' => 'reports'])->syncRoles([$role1, $role2, $role3]);
        Permission::firstOrCreate(['name' => 'starting-bonus', 'description' => 'Starting Bonus', 'module' => 'reports'])->syncRoles([$role1, $role2, $role3]);
        Permission::firstOrCreate(['name' => 'growth-bonus', 'description' => 'Growth Bonus', 'module' => 'reports'])->syncRoles([$role1, $role2, $role3]);
        Permission::firstOrCreate(['name' => 'binarycut.index', 'description' => 'Binary Cut', 'module' => 'config'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'advertisements', 'description' => 'Advertisements', 'module' => 'config'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'action-add-advertisements', 'description' => 'Add', 'action' => 'advertisements'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'action-edit-advertisements', 'description' => 'Edit', 'action' => 'advertisements'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'action-delete-advertisements', 'description' => 'Delete', 'action' => 'advertisements'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'action-list-advertisements', 'description' => 'List', 'action' => 'advertisements'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'action-detail-advertisements', 'description' => 'Detail', 'action' => 'advertisements'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'bank', 'description' => 'Bank', 'module' => 'config'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'action-add-bank', 'description' => 'Add', 'action' => 'bank'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'action-edit-bank', 'description' => 'Edit', 'action' => 'bank'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'action-delete-bank', 'description' => 'Delete', 'action' => 'bank'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'action-list-bank', 'description' => 'List', 'action' => 'bank'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'action-detail-bank', 'description' => 'Detail', 'action' => 'bank'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'payment-method', 'description' => 'Payment Method', 'module' => 'config'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'action-add-payment-method', 'description' => 'Add', 'action' => 'payment-method'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'action-edit-payment-method', 'description' => 'Edit', 'action' => 'payment-method'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'action-delete-payment-method', 'description' => 'Delete', 'action' => 'payment-method'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'action-list-payment-method', 'description' => 'List', 'action' => 'payment-method'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'action-detail-payment-method', 'description' => 'Detail', 'action' => 'payment-method'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'config-settings', 'description' => 'Settings', 'module' => 'config'])->syncRoles([$role1, $role2, $role3]);
        Permission::firstOrCreate(['name' => 'classroom-point-config', 'description' => 'Classroom Point Config', 'module' => 'config'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'action-list-classroom-point-config', 'description' => 'List', 'action' => 'classroom-point-config'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'action-edit-classroom-point-config', 'description' => 'Edit', 'action' => 'classroom-point-config'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'account-type', 'description' => 'Account Type', 'module' => 'config'])->syncRoles([$role1, $role2, $role3]);
        Permission::firstOrCreate(['name' => 'menu-user-scroll', 'description' => 'User-Scroll', 'action' => 'binary-branch'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'users-list', 'description' => 'User-Scroll-List', 'module' => 'organization'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'new-users', 'description' => 'User Request', 'module' => 'organization'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'courses.creation', 'description' => 'Role Request', 'module' => 'organization'])->syncRoles([$role1]);
        Permission::firstOrCreate(['name' => 'frequent-questions', 'description' => 'Frequent questions', 'module' => 'config'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'option', 'description' => 'Option', 'module' => 'config'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'action-list-option', 'description' => 'List', 'action' => 'option'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'action-edit-option', 'description' => 'Edit', 'action' => 'option'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'create', 'description' => 'Badges', 'module' => 'config'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'action-list-badges', 'description' => 'List', 'action' => 'badges'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'action-edit-badges', 'description' => 'Edit', 'action' => 'badges'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'quiz-daily', 'description' => 'Daily', 'action' => 'quiz-daily'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'masterclass.menu', 'description'=> 'Menu Masterclass', 'module' => 'menu-virtual-class'])->syncRoles([$role1, $role2, $role3]);
        Permission::firstOrCreate(['name' => 'masterclass.index', 'description' => 'List Masterclass', 'module' => 'menu-virtual-class'])->syncRoles([$role1, $role2, $role3]);
        Permission::firstOrCreate(['name' => 'masterclass.create', 'description' => 'Create Masterclass', 'action' => 'masterclass.index'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'masterclass.edit', 'description' => 'Edit Masterclass', 'action' => 'masterclass.index'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'masterclass.delete', 'description' => 'Delete Masterclass', 'action' => 'masterclass.index'])->syncRoles([$role1, $role2]);
        Permission::firstOrCreate(['name' => 'masterclass.report', 'description' => 'Reporte de Masterclass', 'module' => 'menu-virtual-class'])->syncRoles([$role1, $role2, $role3]);
        Permission::firstOrCreate(['name' => 'marketing.marketplace.marketplaceIndex', 'description' => 'Marketplace', 'module' => 'menu-virtual-class'])->syncRoles([$role1, $role2, $role3]);
        Permission::firstOrCreate(['name' => 'masterclass.marketplace', 'description' => 'Marketplace Masterclass', 'module' => 'menu-virtual-class'])->syncRoles([$role1, $role2, $role3]);
        Permission::firstOrCreate(['name' => 'masterclass.calendar', 'description'=> 'Calendario de Masterclass', 'module' => 'menu-virtual-class'])->syncRoles([$role1, $role2, $role3]);
    }
}