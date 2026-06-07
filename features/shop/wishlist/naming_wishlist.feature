@wishlist @shop @ui @javascript
Feature: Naming a wishlist
    In order to recognize my saved products later
    As a shop visitor
    I want to rename my wishlist

    Background:
        Given the store operates on a single channel in "United States"

    Scenario: Viewing the default wishlist name
        When I go to my wishlist page
        Then the wishlist name should be displayed as the default wishlist name

    Scenario: Renaming my wishlist
        Given the store has a product "Lexus LS" priced at "$10.00"
        And my wishlist contains product "Lexus LS"
        When I go to my wishlist page
        And I rename my wishlist to "maybe someday"
        Then I should be notified that the wishlist name has been saved
        And the wishlist name should be "maybe someday"
        When I reload my wishlist page
        Then the wishlist name should be "maybe someday"
