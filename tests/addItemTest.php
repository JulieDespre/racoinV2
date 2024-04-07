<?php

use controller\AddItemController;
use PHPUnit\Framework\TestCase;

class addItemTest extends TestCase
{
    private AddItemController $addItemController;

    protected function setUp(): void
    {
        $this->addItemController = new AddItemController();
    }

    public function testValidEmailInputReturnsTrue()
    {
        $isValid = $this->addItemController->isEmail('julie.waltispurger@example.com');
        $this->assertTrue($isValid);
    }

    public function testInvalidEmailInputReturnsFalse()
    {
        $isValid = $this->addItemController->isEmail('invalid email');
        $this->assertFalse($isValid);
    }

    public function testValidPasswordConfirmation()
    {
        $isValid = $this->addItemController->validatePassword('password', 'password');
        $this->assertTrue($isValid);
    }

    public function testInvalidPasswordConfirmation()
    {
        $isValid = $this->addItemController->validatePassword('password', 'differentpassword');
        $this->assertFalse($isValid);
    }

    public function testEmptyFieldsValidation()
    {
        $formData = [
            'nom' => '',
            'email' => 'julie@example.com',
            'phone' => '1234567890',
            'ville' => '',
            'departement' => 75,
            'categorie' => 1,
            'title' => 'Sample Title',
            'description' => 'Sample Description',
            'price' => 100,
            'psw' => 'password',
            'confirm-psw' => 'password'
        ];

        $errors = $this->addItemController->validateFormData($formData);
        $this->assertNotEmpty($errors);
    }

    public function testInvalidEmailValidation()
    {
        $formData = [
            'nom' => 'Julie Waltispurger',
            'email' => 'invalidemail',
            'phone' => '1234567890',
            'ville' => 'Paris',
            'departement' => 75,
            'categorie' => 1,
            'title' => 'Sample Title',
            'description' => 'Sample Description',
            'price' => 100,
            'psw' => 'password',
            'confirm-psw' => 'password'
        ];

        $errors = $this->addItemController->validateFormData($formData);
        $this->assertNotEmpty($errors['emailAdvertiser']);
    }
}

