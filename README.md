# Efficient Vote Bundle

With Symfony you can install access management either with [ACLs](http://symfony.com/doc/master/security/acl.html)
or with [voters](http://symfony.com/doc/master/components/security/authorization.html).
ACLs are hard to implements and voters have performances issues. This bundle
provides a way to enjoy the simplicity of voters without the drawback of
performances.

## Backgroup

The performance issue of voters comes from the `AccessDecisionManager` service.
His role is to ask every voter to know if the current user is allowed to perform
a given action (e.g. `ROLE_SUPER_ADMIN`). The problem is that this service is not
aware that most of the voters will abstain as they don't support the attribute.

So, if your application has 10 voters and your template uses 10 times the
`is_granted()` function, it makes 100 calls. If your application grows, this will
become a bottleneck.

To solve that, this bundle overwrites the default  `AccessDecisionManager` service.
With the new service, you can register your voters and specify which attributes
are supported. The access decision manager will so not call your voter if he's not
competent for the attribute.

## Installation

```
composer require kinulab/efficient-vote-bundle
```

Then add in you `app/config/AppKernel.php` :

```php
    public function registerBundles()
    {
        $bundles = [
            // [...]
            new Kinulab\EfficientVoteBundle\KinulabEfficientVoteBundle(),
        ];
```

## Usage

The new access decision manager will assume that you organize your voter by types
and by domains.

Your must then name your roles with this forms:

```
ROLE _ HOUSE _ OPEN_DOOR
|__|   |___|   |_______|
type   domain  attribute
```

Your voters class remain exaclty like the standards symfony voters. The only
difference is on the registration of your voter service.

With a standard vorter, your service is registered like that:

```yaml
# app/config/services.yml
services:
    app.host_voter:
        class: AppBundle\Security\hostVoter
        # small performance boost
        public: false
        tags:
            - { name: security.voter }

```

With this bundle you should now register your service like that :

```yaml
# app/config/services.yml
services:
    app.host_voter:
        class: AppBundle\Security\hostVoter
        # small performance boost
        public: false
        tags:
            # big performance boost
            - { name: security.efficient_voter, type: ROLE, domain: HOUSE }
```

With this configuration, the voter above, will be called only for the
`ROLE_HOUSE_*` attributes.

## Note

The new access decision manager remains compatible with the original. So if
you have some voters that are registered the old way, they will still work as
expected (but called tons of time).