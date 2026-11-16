ALTER TABLE availability_blocks
  DROP CONSTRAINT IF EXISTS availability_blocks_block_date_key;
ALTER TABLE availability_blocks
  ADD COLUMN IF NOT EXISTS slot TEXT NOT NULL DEFAULT 'all'
    CHECK (slot IN ('overnight','daycation','all'));
ALTER TABLE availability_blocks
  DROP CONSTRAINT IF EXISTS availability_blocks_block_date_slot_key;
ALTER TABLE availability_blocks
  ADD CONSTRAINT availability_blocks_block_date_slot_key UNIQUE (block_date, slot);
