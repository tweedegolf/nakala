Feature: Converting HTML snippets to PHPWord commands
  In order to create word documents
  As a user
  I need to be able to convert html to word

  Scenario: Converting a document
    Given the document "test.html" is provided
    When I convert the document
    Then it should be the same as "test.doc"
