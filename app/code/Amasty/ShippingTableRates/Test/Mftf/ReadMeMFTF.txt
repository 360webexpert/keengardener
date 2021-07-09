ReadeMeMFTF (recommendations for running tests related to Shipping Table Rates extension).

    30 Shipping Table Rates specific tests, grouped by purpose, for greater convenience.

        Tests group: ShippingTableRates
        Runs 24 tests.
            SSH command to run this group of tests:
            vendor/bin/mftf run:group ShippingTableRates -r

        Tests group: STRCheckShippingTypeSuite
        Runs 6 tests related to Shipping Type.
        This tests have preconditions, so they should be run in a suite.
            SSH command to run this group of tests:
            vendor/bin/mftf run:group STRCheckShippingTypeSuite -r