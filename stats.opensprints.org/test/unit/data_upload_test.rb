require 'test_helper'

class DataUploadTest < ActiveSupport::TestCase
  context "DataUpload" do
    should_belong_to :user
    should_validate_presence_of :user
    should_have_attached_file :database
    context "interaction with the attached database" do
      setup do
        `cp test/fixtures/opensprints_data.db /tmp/test.db`
        db = Sequel.sqlite("/tmp/test.db")
        
        Tournament.db = db
        Tournament.create(:name => "three")
        @event = Tournament.create(:name => "two")
        Tournament.create(:name => "one")
        @du = DataUpload.new
        @du.database.stubs(:path).returns("/tmp/test.db")
        assert_equal "/tmp/test.db",@du.database.path
      end
      should "return the tournaments from the datafile" do
        assert_equal ["one","two","three"].sort,@du.tournaments.map(&:name).sort
      end
      should "load specific tournaments" do
        assert_equal "two", @du.get_event(@event.pk).name
      end
    end
  end
end
