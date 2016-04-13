# How to contribute

RadPHP loves to welcome your contributions. There are several ways to help out:

* Create an [issue](https://github.com/radphp/radphp/issues) on GitHub, if you have found a bug
* Write test cases for open bug issues
* Write patches for open bug/feature issues, preferably with test cases included

There are a few guidelines that we need contributors to follow so that we have a
chance of keeping on top of things.


## Getting Started

* Make sure you have a [GitHub account](https://github.com/signup/free).
* Submit an [issue](https://github.com/radphp/radphp/issues), assuming one does not already exist.
  * Clearly describe the issue including steps to reproduce when it is a bug.
  * Make sure you fill in the earliest version that you know has the issue.
* Fork the repository on GitHub.

## Making Changes

* Create a topic branch from where you want to base your work.
  * This is usually the master branch.
  * Only target release branches if you are certain your fix must be on that
    branch.
  * To quickly create a topic branch based on master; `git branch
    master/my_contribution master` then checkout the new branch with `git
    checkout master/my_contribution`. Better avoid working directly on the
    `master` branch, to avoid conflicts if you pull in updates from origin.
* Make commits of logical units.
* Check for unnecessary whitespace with `git diff --check` before committing.
* Use descriptive commit messages and reference the #issue number.
* Core test cases should continue to pass. You can run tests locally or enable
  [travis-ci](https://travis-ci.org/) for your fork, so all tests and codesniffs
  will be executed.
* Your work should apply the [PSR-1](http://www.php-fig.org/psr/psr-1)/[PSR-2](http://www.php-fig.org/psr/psr-2).

## Which branch to base the work

* Bugfix branches will be based on master.
* New features that are backwards compatible will be based on the appropriate 'next' branch. For example if you want to contribute to the next 1.x branch, you should base your changes on `1.next`.
* New features or other non backwards compatible changes will go in the next major release branch. Development on 2.0 has not started yet, so breaking changes are unlikely to be merged in.

## Submitting Changes

* Push your changes to a topic branch in your fork of the repository.
* Submit a pull request to the repository in the RadPHP organization, with the
  correct target branch.

## Test cases and codesniffer

To run the test cases locally use the following command:

    bin/phpunit

You can also register on [Travis CI](https://travis-ci.org/) and from your
[profile](https://travis-ci.org/profile) page enable the service hook for your
RadPHP fork on GitHub for automated test builds.

To run the sniffs for PSR2,PSR1 coding standards:

    bin/phpcs -p --extensions=php --standard=PSR2,PSR1 ./src

## Reporting a Security Issue

If you've found a security related issue in RadPHP, please don't open an issue in github. Instead contact us at radphp.org@gmail.com.

# Additional Resources

* [Existing issues](https://github.com/radphp/radphp/issues)
* [General GitHub documentation](https://help.github.com/)
* [GitHub pull request documentation](https://help.github.com/send-pull-requests/)
