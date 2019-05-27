<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        $arrayRoles = [
            'ROLE_USER',
            'ROLE_ADMIN'
        ];

        $arrayGender = [
            "male",
            "female"
        ];

        // Fake user data for table
        for ($i=0; $i < 10; $i++) { 
            $roles = [$arrayRoles[mt_rand(0, count($arrayRoles) - 1)]];
            $gender = $arrayGender[mt_rand(0, count($arrayGender) - 1)];

            $user = new User();

            $user->setFirstName($faker->firstName($gender));
            $user->setLastName($faker->lastName);
            $user->setEmail($faker->email);
            $user->setUsername($faker->userName);
            $user->setRoles($roles);
            $user->setPassword($this->encoder->encodePassword($user, 'test'));

            $manager->persist($user);
        }

        // User data for test roles
        foreach ($this->getUserData() as [$firstName, $lastName, $email, $userName, $roles, $password]) {
            $user = new User();

            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setEmail($email);
            $user->setUsername($userName);
            $user->setRoles($roles);
            $user->setPassword($this->encoder->encodePassword($user, $password));

            $manager->persist($user);
        }

        $manager->flush();
    }

    public function getUserData()
    {
        return [
            ["Admin", "Istrateur", "admin@admin.fr", "admin", ["ROLE_ADMIN"], "admin"],
            ["User", "Pateur", "user@user.fr", "user", ["ROLE_USER"], "user"]
        ];
    }
}
