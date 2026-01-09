<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        try {
            // Handle renaming of companies table
            if (Schema::hasTable('companies')) {
                // Use raw SQL to rename columns in companies table
                DB::statement('ALTER TABLE `companies` RENAME COLUMN `company_code` TO `organization_code`');
                DB::statement('ALTER TABLE `companies` RENAME COLUMN `company_name` TO `organization_name`');
                
                // Rename optional columns if they exist
                if (Schema::hasColumn('companies', 'company_logo')) {
                    DB::statement('ALTER TABLE `companies` RENAME COLUMN `company_logo` TO `logo_path`');
                }
                if (Schema::hasColumn('companies', 'company_address')) {
                    DB::statement('ALTER TABLE `companies` RENAME COLUMN `company_address` TO `address`');
                }
                if (Schema::hasColumn('companies', 'company_phone')) {
                    DB::statement('ALTER TABLE `companies` RENAME COLUMN `company_phone` TO `phone`');
                }
                if (Schema::hasColumn('companies', 'company_email')) {
                    DB::statement('ALTER TABLE `companies` RENAME COLUMN `company_email` TO `email`');
                }
                
                // Finally rename the table itself
                DB::statement('RENAME TABLE `companies` TO `organizations`');
            } else if (Schema::hasTable('organizations')) {
                // Table already renamed, just ensure all columns are renamed
                if (Schema::hasColumn('organizations', 'company_code')) {
                    DB::statement('ALTER TABLE `organizations` RENAME COLUMN `company_code` TO `organization_code`');
                }
                if (Schema::hasColumn('organizations', 'company_name')) {
                    DB::statement('ALTER TABLE `organizations` RENAME COLUMN `company_name` TO `organization_name`');
                }
                if (Schema::hasColumn('organizations', 'company_logo')) {
                    DB::statement('ALTER TABLE `organizations` RENAME COLUMN `company_logo` TO `logo_path`');
                }
                if (Schema::hasColumn('organizations', 'company_address')) {
                    DB::statement('ALTER TABLE `organizations` RENAME COLUMN `company_address` TO `address`');
                }
                if (Schema::hasColumn('organizations', 'company_phone')) {
                    DB::statement('ALTER TABLE `organizations` RENAME COLUMN `company_phone` TO `phone`');
                }
                if (Schema::hasColumn('organizations', 'company_email')) {
                    DB::statement('ALTER TABLE `organizations` RENAME COLUMN `company_email` TO `email`');
                }
            }
            
            // Rename columns in users table
            if (Schema::hasTable('users') && Schema::hasColumn('users', 'company_id')) {
                DB::statement('ALTER TABLE `users` RENAME COLUMN `company_id` TO `organization_id`');
            }
            
            // Rename columns in queues table
            if (Schema::hasTable('queues') && Schema::hasColumn('queues', 'company_id')) {
                DB::statement('ALTER TABLE `queues` RENAME COLUMN `company_id` TO `organization_id`');
            }
            
            // Handle company_settings table
            if (Schema::hasTable('company_settings')) {
                if (Schema::hasColumn('company_settings', 'company_id')) {
                    DB::statement('ALTER TABLE `company_settings` RENAME COLUMN `company_id` TO `organization_id`');
                }
                DB::statement('RENAME TABLE `company_settings` TO `organization_settings`');
            } else if (Schema::hasTable('organization_settings')) {
                // Table already renamed, just ensure column is renamed
                if (Schema::hasColumn('organization_settings', 'company_id')) {
                    DB::statement('ALTER TABLE `organization_settings` RENAME COLUMN `company_id` TO `organization_id`');
                }
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        try {
            // Rename organization_settings back to company_settings
            if (Schema::hasTable('organization_settings')) {
                if (Schema::hasColumn('organization_settings', 'organization_id')) {
                    DB::statement('ALTER TABLE `organization_settings` RENAME COLUMN `organization_id` TO `company_id`');
                }
                DB::statement('RENAME TABLE `organization_settings` TO `company_settings`');
            }
            
            // Rename columns in queues table back
            if (Schema::hasTable('queues') && Schema::hasColumn('queues', 'organization_id')) {
                DB::statement('ALTER TABLE `queues` RENAME COLUMN `organization_id` TO `company_id`');
            }
            
            // Rename columns in users table back
            if (Schema::hasTable('users') && Schema::hasColumn('users', 'organization_id')) {
                DB::statement('ALTER TABLE `users` RENAME COLUMN `organization_id` TO `company_id`');
            }
            
            // Rename organizations table back to companies
            if (Schema::hasTable('organizations')) {
                if (Schema::hasColumn('organizations', 'organization_code')) {
                    DB::statement('ALTER TABLE `organizations` RENAME COLUMN `organization_code` TO `company_code`');
                }
                if (Schema::hasColumn('organizations', 'organization_name')) {
                    DB::statement('ALTER TABLE `organizations` RENAME COLUMN `organization_name` TO `company_name`');
                }
                if (Schema::hasColumn('organizations', 'logo_path')) {
                    DB::statement('ALTER TABLE `organizations` RENAME COLUMN `logo_path` TO `company_logo`');
                }
                if (Schema::hasColumn('organizations', 'address')) {
                    DB::statement('ALTER TABLE `organizations` RENAME COLUMN `address` TO `company_address`');
                }
                if (Schema::hasColumn('organizations', 'phone')) {
                    DB::statement('ALTER TABLE `organizations` RENAME COLUMN `phone` TO `company_phone`');
                }
                if (Schema::hasColumn('organizations', 'email')) {
                    DB::statement('ALTER TABLE `organizations` RENAME COLUMN `email` TO `company_email`');
                }
                
                DB::statement('RENAME TABLE `organizations` TO `companies`');
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }
};
