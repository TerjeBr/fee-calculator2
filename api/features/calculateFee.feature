Feature: Calculate the fee for a loan application

  Scenario: Upload application and calculate fee
    Given I add "Accept" header equal to "application/json"
    When I send a "POST" request to "/api/applications" with body:
    """
    {
        term: 24
        amount: 2750
    }
    """
    And print last response
