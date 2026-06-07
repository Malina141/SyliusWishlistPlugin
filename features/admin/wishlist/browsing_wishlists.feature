@wishlist @admin @ui
Feature: Browsing wishlists in the admin panel
    In order to support customers and inspect wishlist usage
    As an administrator
    I want to browse and inspect guest and customer wishlists

    Background:
        Given the store operates on a single channel in "United States"
        And I am logged in as an administrator

    Scenario: Seeing guest and customer wishlists in the admin grid
        Given there is a customer account "driver@example.com"
        And the store has a product "BMW M3 E30" priced at "$10.00"
        And customer "driver@example.com" has a wishlist named "M3 collection" containing product "BMW M3 E30"
        And there is a guest wishlist named "Anonymous picks" containing product "BMW M3 E30"
        When I browse wishlists in the admin panel
        Then I should see wishlist "M3 collection" in the admin wishlist list
        And I should see owner "driver@example.com" for wishlist "M3 collection"
        And I should see "1" as the number of items for wishlist "M3 collection"
        And I should see wishlist "Anonymous picks" in the admin wishlist list
        And I should see owner "Guest" for wishlist "Anonymous picks"

    Scenario: Searching wishlists by name
        Given there is a wishlist named "M3 collection"
        And there is a wishlist named "Track classics"
        When I browse wishlists in the admin panel
        And I search admin wishlists for "M3"
        Then I should see wishlist "M3 collection" in the admin wishlist list
        And I should not see wishlist "Track classics" in the admin wishlist list

    Scenario: Filtering wishlists by channel
        Given the store also operates on another channel named "Europe"
        And there is a wishlist named "US wishlist" on the "United States" channel
        And there is a wishlist named "EU wishlist" on the "Europe" channel
        When I browse wishlists in the admin panel
        And I filter admin wishlists by the "United States" channel
        Then I should see wishlist "US wishlist" in the admin wishlist list
        And I should not see wishlist "EU wishlist" in the admin wishlist list

    Scenario: Filtering wishlists by registered customer ownership
        Given there is a customer account "driver@example.com"
        And there is a customer wishlist named "Registered wishlist" owned by "driver@example.com"
        And there is a guest wishlist named "Guest wishlist"
        When I browse wishlists in the admin panel
        And I filter admin wishlists to registered customers
        Then I should see wishlist "Registered wishlist" in the admin wishlist list
        And I should not see wishlist "Guest wishlist" in the admin wishlist list
        When I filter admin wishlists to guests
        Then I should see wishlist "Guest wishlist" in the admin wishlist list
        And I should not see wishlist "Registered wishlist" in the admin wishlist list

    Scenario: Viewing wishlist details in the admin panel
        Given there is a customer account "driver@example.com"
        And the store has a product "BMW M3 E30" priced at "$10.00"
        And customer "driver@example.com" has a wishlist named "M3 collection" containing product "BMW M3 E30"
        When I view wishlist "M3 collection" in the admin panel
        Then I should see wishlist name "M3 collection" in the admin wishlist details
        And I should see owner "driver@example.com" in the admin wishlist details
        And I should see the "United States" channel in the admin wishlist details
        And I should see product "BMW M3 E30" in the admin wishlist details
        And I should see the variant code of product "BMW M3 E30" in the admin wishlist details

    Scenario: Viewing an empty wishlist in the admin panel
        Given there is a guest wishlist named "Empty picks"
        When I view wishlist "Empty picks" in the admin panel
        Then I should see wishlist name "Empty picks" in the admin wishlist details
        And I should be notified that the wishlist has no items in the admin wishlist details
