Factory.sequence :email do |n|
  "user#{n}@example.com"
end

Factory.sequence :group_name do |n|
  "NYC Goldsprints #{n}"
end

Factory.define :user do |user|
  user.email                 { Factory.next :email }
  user.password              { "password" }
  user.password_confirmation { "password" }
  user.group_name { Factory.next :group_name }
end

Factory.define :email_confirmed_user, :parent => :user do |user|
  user.email_confirmed { true }
end
