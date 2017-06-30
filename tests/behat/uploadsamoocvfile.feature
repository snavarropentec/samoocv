@user @profilefield  @profilefield_samoocv
Feature: Test the generation of a custom user profile field data
  In order to upload a samoocv custom profile field
  As an user
  I need to edit my profile and upload a file in the correct field

  Background: 
    Given the following "users" exist:
      | username | firstname | lastname | email             |
      | user1    | User      | 1        | user1@example.com |
      | user2    | User      | 2        | user2@example.com |
      | user3    | User      | 3        | user3@example.com |
    And the following "categories" exist:
      | name | idnumber |
      | Cat1 | CAT1     |
    And the following "courses" exist:
      | category | shortname | idnumber |
      | CAT1     | M01       | C1       |
      | CAT1     | M02       | C2       |
    And the following "course enrolments" exist:
      | user  | course | role           |
      | user1 | M01    | student        |
      | user1 | M02    | student        |
      | user2 | M01    | editingteacher |
      | user2 | M02    | editingteacher |
      | user3 | M01    | student        |
      | user3 | M02    | student        |

  @javascript
  Scenario: Create the user custom profile field of samoocv type.
    # Log in as an admin.
    Given I log in as "admin"
    # Create a new user custom profile field.
    And I navigate to "Users > Accounts > User profile fields" in site administration
    And I set the field "datatype" to "samoocv"
    Then I should see "Creating a new 'Curriculum Vitae Files' profile field"
    # Editing the form of the new field.
    And I set the field "shortname" to "TestSamooCV"
    And I set the field with xpath "//input[@id='id_name']" to "Test Samoo CV"
    And I set the field "description[text]" to "Sample of description"
    And I press "Save changes"
    # See the new field under Other fields.
    Then I should see "Samoo CV"
    And I log out

  @javascript
  Scenario: Upload a file in the newly created profile field.
    # Log in as an admin.
    Given I log in as "admin"
    # Create a new user custom profile field.
    And I navigate to "Users > Accounts > User profile fields" in site administration
    And I set the field "datatype" to "samoocv"
    Then I should see "Creating a new 'Curriculum Vitae Files' profile field"
    # Editing the form of the new field.
    And I set the field "shortname" to "TestSamooCV"
    And I set the field with xpath "//input[@id='id_name']" to "Test Samoo CV"
    And I set the field "description[text]" to "Sample of description"
    And I press "Save changes"
    # See the new field under Other fields.
    Then I should see "Samoo CV"
    And I log out
    # Log in as user1.
    Given I log in as "user1"
    # Navigate to own profile
    And I go to my profile page
    # Edit profile uploading a file to the new custom user field.
    And I follow "Edit profile"
    And I expand all fieldsets
    Then I upload "/user/profile/field/samoocv/tests/fixtures/test.pdf" file to "Test Samoo CV" filemanager
    And I press "Update profile"
    # Check the visibility and that the file downloads successfully.
    And I go to my profile page
    Then I should see "Samoo CV"
    And I should see "test.pdf"
    And I follow "test.pdf"
    And I wait "10" seconds

  @javascript
  Scenario: Test the visibility of the field to other users.
    # Log in as an admin.
    Given I log in as "admin"
    # Create a new user custom profile field.
    And I navigate to "Users > Accounts > User profile fields" in site administration
    And I set the field "datatype" to "samoocv"
    Then I should see "Creating a new 'Curriculum Vitae Files' profile field"
    # Editing the form of the new field.
    And I set the field "shortname" to "TestSamooCV"
    And I set the field with xpath "//input[@id='id_name']" to "Test Samoo CV"
    And I set the field "description[text]" to "Sample of description"
    And I press "Save changes"
    # See the new field under Other fields.
    Then I should see "Samoo CV"
    And I log out
    # Log in as user1.
    Given I log in as "user1"
    # Navigate to own profile
    And I go to my profile page
    # Edit profile uploading a file to the new custom user field.
    And I follow "Edit profile"
    And I expand all fieldsets
    Then I upload "/user/profile/field/samoocv/tests/fixtures/test.pdf" file to "Test Samoo CV" filemanager
    And I press "Update profile"
    # Check the visibility for the own user and that the file downloads successfully.
    And I go to my profile page
    Then I should see "Samoo CV"
    And I should see "test.pdf"
    And I follow "test.pdf"
    And I wait "10" seconds
    And I log out
    # Check the visibility for the teacher in the same course as the user1 and that the file downloads successfully.
    # Log in as user2.
    Given I log in as "user2"
    # Navigate to user1 profile.
    And I visit the profile of "user1"
    Then I should see "Samoo CV"
    And I should see "test.pdf"
    And I follow "test.pdf"
    And I wait "10" seconds
    And I log out
    # Check the visibility for other student in the same course as the user1.
    # Log in as user3.
    Given I log in as "user3"
    # Navigate to user1 profile.
    And I visit the profile of "user1"
    # As other students cant see the custom profile field, user3 should not see the field.
    Then I should see "Samoo CV"
    And I should see "No permissions to see this content"
    And I wait "10" seconds
    And I log out
