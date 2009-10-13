class AddGroupNameToUsers < ActiveRecord::Migration
  def self.up
    change_table :users do |t|
      t.string :group_name
      t.string :group_name_for_url
    end
  end

  def self.down
    remove_column :users, :group_name
    remove_column :users, :group_name_for_url
  end
end
