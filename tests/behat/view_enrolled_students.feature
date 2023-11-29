@report @report_apprenticeoffjob @sol
Feature: View all Completed and Target hours
  As a teacher
  In order to see how many hours students have logged
  I need to see a list of all enrolled students with their hours

  Background:
    Given the following "courses" exist:
    | shortname | fullname | idnumber              |
    | C1        | Course 1 | ABC101_A_SEM1_2023/24 |
    And the following "users" exist:
    | username  | firstname | lastname | email                |
    | student1  | Student   | 1        | student1@example.com |
    | student2  | Student   | 2        | student2@example.com |
    | teacher1  | Teacher   | 1        | teacher1@example.com |
    And the following "course enrolments" exist:
    | user      | course | role    |
    | teacher1  | C1     | teacher |

  Scenario: No students are enrolled
    Given I am on the "Course 1" "Course" page logged in as "teacher1"
    When I navigate to "Reports > Apprentice off the job hours report" in current page administration
    Then I should see "No hours to display"

  @javascript @_file_upload
  Scenario: Student has logged hours
    Given the following "course enrolments" exist:
    | user      | course | role    |
    | student1  | C1     | student |
    | student2  | C1     | student |
    And I am on the "Course 1" "Course" page logged in as "teacher1"
    When I navigate to "Reports > Apprentice off the job hours report" in current page administration
    And the following should exist in the "apprenticeoffjob-targethours-table" table:
    | Student   | Teaching of Theory | Practical Training | Assignments, Projects & Portfolio (SDS) | Work Shadowing | Mentoring | Completed/Target | Commitment Statement | Actions |
    | Student 1 |                    |                    |                                         |                |           | 0 / 0            |                      | Edit    |
    | Student 2 |                    |                    |                                         |                |           | 0 / 0            |                      | Edit    |
    And I click on "Edit" "link" in the "Student 1" "table_row"
    And I set the following fields to these values:
    | Teaching of Theory                      | 10 |
    | Practical Training                      | 10 |
    | Assignments, Projects & Portfolio (SDS) | 10 |
    | Work Shadowing                          | 10 |
    | Mentoring                               | 0  |
    And I press "Save changes"
    And I click on "Edit" "link" in the "Student 2" "table_row"
    And I set the following fields to these values:
    | Teaching of Theory                      |      |
    | Practical Training                      | 20   |
    | Assignments, Projects & Portfolio (SDS) | 25   |
    | Work Shadowing                          | 35   |
    | Mentoring                               | 1    |
    And I upload "lib/tests/fixtures/empty.txt" file to "Commitment statement" filemanager
    And I press "Save changes"
    Then the following should exist in the "apprenticeoffjob-targethours-table" table:
    | Student   | Teaching of Theory | Practical Training | Assignments, Projects & Portfolio (SDS) | Work Shadowing | Mentoring | Completed/Target | Commitment Statement | Actions |
    | Student 1 | 0 / 10             | 0 / 10             | 0 / 10                                  | 0 / 10         |           | 0 / 40           |                      | Edit    |
    | Student 2 |                    | 0 / 20             | 0 / 25                                  | 0 / 35         | 0 / 1     | 0 / 81           |                      | Edit    |
