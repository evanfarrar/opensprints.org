class CreateDataUploads < ActiveRecord::Migration
  def self.up
    create_table :data_uploads do |t|
      t.integer   :user_id
      t.string    :database_file_name
      t.string    :database_content_type
      t.integer   :database_file_size
      t.datetime  :database_updated_at

      t.timestamps
    end
  end

  def self.down
    drop_table :data_uploads
  end
end
