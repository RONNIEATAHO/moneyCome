-- Migration: Add discount_price column to products table
-- Run this if you have an existing database that needs to be updated

-- Add discount_price column to products table if it doesn't exist
ALTER TABLE products ADD COLUMN discount_price DECIMAL(10, 2) NULL AFTER price;
