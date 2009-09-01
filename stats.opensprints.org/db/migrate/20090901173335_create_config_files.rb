class CreateConfigFiles < ActiveRecord::Migration
  def self.up
    create_table :config_files do |t|
      t.integer   :user_id
      t.string    :config_file_name
      t.string    :config_content_type
      t.integer   :config_file_size
      t.datetime  :config_updated_at

      t.timestamps
    end
  end

  def self.down
    drop_table :config_files
  end
end
