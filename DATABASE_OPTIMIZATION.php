<?php

/**
 * DATABASE OPTIMIZATION - ADD INDEXES FOR BETTER PERFORMANCE
 * 
 * Run these commands in MySQL/phpMyAdmin to optimize queries
 */

// For QMS - Add these indexes to speed up common queries:

// users table indexes
ALTER TABLE users ADD INDEX idx_role (role);
ALTER TABLE users ADD INDEX idx_company_id (company_id);
ALTER TABLE users ADD INDEX idx_is_online (is_online);
ALTER TABLE users ADD INDEX idx_role_online (role, is_online);

// queues table indexes (MOST IMPORTANT for performance)
ALTER TABLE queues ADD INDEX idx_counter_id_status (counter_id, status);
ALTER TABLE queues ADD INDEX idx_status (status);
ALTER TABLE queues ADD INDEX idx_status_updated_at (status, updated_at);
ALTER TABLE queues ADD INDEX idx_counter_status_updated (counter_id, status, updated_at);
ALTER TABLE queues ADD INDEX idx_queue_number (queue_number);
ALTER TABLE queues ADD INDEX idx_created_at (created_at);
ALTER TABLE queues ADD INDEX idx_updated_at (updated_at);
ALTER TABLE queues ADD UNIQUE INDEX uq_queue_number (queue_number);

// company_settings table indexes
ALTER TABLE company_settings ADD INDEX idx_company_id (company_id);

// marquee_settings table indexes
ALTER TABLE marquee_settings ADD INDEX idx_company_id (company_id);

// videos table indexes
ALTER TABLE videos ADD INDEX idx_company_id (company_id);
ALTER TABLE videos ADD INDEX idx_status (status);

// video_controls table indexes
ALTER TABLE video_controls ADD INDEX idx_company_id (company_id);

/**
 * QUERY OPTIMIZATION TIPS:
 * 
 * 1. AVOID N+1 QUERIES
 *    Use eager loading:
 *    $queues = Queue::with('counter')->where('status', 'waiting')->get();
 *    Instead of:
 *    $queues = Queue::where('status', 'waiting')->get();
 *    foreach($queues as $q) { $counter = $q->counter; }
 *
 * 2. USE ONLY NEEDED COLUMNS
 *    Select::select('id', 'name', 'email')->get();
 *    Instead of:
 *    Select::get();
 *
 * 3. USE PAGINATION FOR LARGE DATASETS
 *    Queue::paginate(50);
 *    Instead of:
 *    Queue::get(); // Could be thousands of rows
 *
 * 4. CACHE EXPENSIVE QUERIES
 *    Cache::remember('key', 60, function() {
 *        return Queue::count();
 *    });
 */
