<?php


namespace Tests\Integration;


use Adldap\Laravel\Facades\Adldap;
use Tests\TestCase;

class Adldap2Test extends TestCase
{
    /** @test
     *  @doesNotPerformAssertions
     * */
    public function it_connects_to_active_directory() {
        try {
            $provider = Adldap::getDefaultProvider()->connect();
        } catch (\Adldap\Auth\BindException $e) {
            $this->fail('Can\'t contact LDAP server');
        }
    }

    /** @test */
    public function it_authenticates_with_valid_credentials() {
        $this->assertTrue(Adldap::auth()->attempt(
            config('adldap.connections.default.connection_settings.admin_username'),
            config('adldap.connections.default.connection_settings.admin_password')
        ));
    }
}
