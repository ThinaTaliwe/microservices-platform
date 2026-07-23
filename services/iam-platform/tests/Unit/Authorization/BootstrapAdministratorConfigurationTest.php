<?php

namespace Tests\Unit\Authorization;

use Tests\TestCase;

class BootstrapAdministratorConfigurationTest extends TestCase
{
    public function test_bootstrap_administrator_emails_are_valid(): void
    {
        $emails = config('bootstrap-admins.emails');

        $this->assertIsArray($emails);
        $this->assertNotEmpty($emails);

        foreach ($emails as $email) {
            $this->assertIsString($email);

            $this->assertSame(
                strtolower(trim($email)),
                $email
            );

            $this->assertNotFalse(
                filter_var(
                    $email,
                    FILTER_VALIDATE_EMAIL
                )
            );
        }

        $this->assertSame(
            array_values(array_unique($emails)),
            array_values($emails)
        );
    }

    public function test_required_bootstrap_administrators_are_configured(): void
    {
        $emails = config('bootstrap-admins.emails');

        $this->assertContains(
            'korrie@bchem.co.za',
            $emails
        );

        $this->assertContains(
            'thina.taliwe2@gmail.com',
            $emails
        );
    }
}
