require 'test_helper'

class DataUploadTest < ActiveSupport::TestCase
  context "DataUpload" do
    should_belong_to :user
    should_have_attached_file :database
  end
end
