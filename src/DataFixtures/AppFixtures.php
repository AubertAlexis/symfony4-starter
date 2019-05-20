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

        $optionalRoles = [
            'ROLE_USER',
            'ROLE_ADMIN'
        ];

        for ($i=0; $i < 10; $i++) { 
            $roles = [$optionalRoles[mt_rand(0, count($optionalRoles) - 1)]];
            
            $user = new User();

            $user->setEmail($faker->email);
            $user->setUsername($faker->userName);
            $user->setRoles($roles);
            $user->setPassword($this->encoder->encodePassword($user, 'test'));

            $manager->persist($user);
        }

        $manager->flush();
    }
}
