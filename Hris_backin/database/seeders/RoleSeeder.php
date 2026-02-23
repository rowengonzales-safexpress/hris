<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // Ticket permissions
            'view_tickets',
            'create_tickets',
            'edit_tickets',
            'delete_tickets',
            'assign_tickets',
            'close_tickets',
            'reopen_tickets',
            'view_all_tickets',
            'view_department_tickets',
            
            // Reply permissions
            'create_replies',
            'edit_replies',
            'delete_replies',
            'create_internal_notes',
            
            // Department permissions
            'view_departments',
            'create_departments',
            'edit_departments',
            'delete_departments',
            'manage_department_users',
            
            // User permissions
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'manage_user_roles',
            
            // Knowledge Base permissions
            'view_kb_articles',
            'create_kb_articles',
            'edit_kb_articles',
            'delete_kb_articles',
            'publish_kb_articles',
            
            // System permissions
            'view_admin_dashboard',
            'manage_system_settings',
            'view_reports',
            'export_data',
            'manage_custom_fields',
            
            // File permissions
            'upload_attachments',
            'download_attachments',
            'delete_attachments',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        
        // Super Admin Role
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->syncPermissions(Permission::all());

        // Admin Role
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions([
            'view_tickets', 'create_tickets', 'edit_tickets', 'delete_tickets',
            'assign_tickets', 'close_tickets', 'reopen_tickets', 'view_all_tickets',
            'create_replies', 'edit_replies', 'delete_replies', 'create_internal_notes',
            'view_departments', 'create_departments', 'edit_departments', 'delete_departments',
            'manage_department_users',
            'view_users', 'create_users', 'edit_users', 'delete_users', 'manage_user_roles',
            'view_kb_articles', 'create_kb_articles', 'edit_kb_articles', 'delete_kb_articles',
            'publish_kb_articles',
            'view_admin_dashboard', 'manage_system_settings', 'view_reports', 'export_data',
            'manage_custom_fields',
            'upload_attachments', 'download_attachments', 'delete_attachments',
        ]);

        // Manager Role
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->syncPermissions([
            'view_tickets', 'create_tickets', 'edit_tickets', 'assign_tickets',
            'close_tickets', 'reopen_tickets', 'view_department_tickets',
            'create_replies', 'edit_replies', 'create_internal_notes',
            'view_departments', 'manage_department_users',
            'view_users', 'edit_users',
            'view_kb_articles', 'create_kb_articles', 'edit_kb_articles', 'publish_kb_articles',
            'view_reports', 'export_data',
            'upload_attachments', 'download_attachments',
        ]);

        // Agent Role
        $agent = Role::firstOrCreate(['name' => 'agent']);
        $agent->syncPermissions([
            'view_tickets', 'edit_tickets', 'view_department_tickets',
            'create_replies', 'create_internal_notes',
            'view_departments',
            'view_kb_articles', 'create_kb_articles', 'edit_kb_articles',
            'upload_attachments', 'download_attachments',
        ]);

        // Customer Role
        $customer = Role::firstOrCreate(['name' => 'customer']);
        $customer->syncPermissions([
            'view_tickets', 'create_tickets',
            'create_replies',
            'view_kb_articles',
            'upload_attachments', 'download_attachments',
        ]);

        // Employee Role (for internal users who can create tickets but aren't agents)
        $employee = Role::firstOrCreate(['name' => 'employee']);
        $employee->syncPermissions([
            'view_tickets', 'create_tickets',
            'create_replies',
            'view_kb_articles',
            'upload_attachments', 'download_attachments',
        ]);
    }
}