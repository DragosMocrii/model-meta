# Laravel Model Meta

## Introduction

The Model Meta package for Laravel allows you to easily store and retrieve meta data for any models. This package is an implementation of the Property Bag pattern, which is supposed to help you deal with the situations when you need to store various model properties (meta), but adding the properties to the model is not an option. 

## Test Setup

You will need to set the following details locally and on your Stripe account in order to run the Cashier unit tests:

### Local

#### .env

    STRIPE_KEY=
    STRIPE_SECRET=
    STRIPE_MODEL=User

### Stripe

#### Plans

    * monthly-10-1 ($10)
    * monthly-10-2 ($10)

#### Coupons

    * coupon-1 ($5)

## Official Documentation

Documentation for Cashier can be found on the [Laravel website](http://laravel.com/docs/billing).

## Contributing

Thank you for considering contributing to the Cashier. You can read the contribution guide lines [here](contributing.md).

## License

Laravel Cashier is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
