class AddGroupNameToUsers < ActiveRecord::Migration
  def self.up
    change_table :users do |t|
      t.string :group_name
    end
  end

  def self.down
    remove_column :users, :group_name
  end
end
