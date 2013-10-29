<?php

/**
 * @author mahmoud
 */

namespace Objects\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Objects\UserBundle\Entity\User;
use Objects\UserBundle\Entity\Role;

class LoadUserData implements FixtureInterface {

    public function load(ObjectManager $manager) {
        // load the main required roles

        // create the ROLE_ACTIVE_SOCIAL role
        $roleActiveSocial = new Role();
        $roleActiveSocial->setName('ROLE_ACTIVE_SOCIAL');
        $manager->persist($roleActiveSocial);

        // create the ROLE_NOTACTIVE_SOCIAL role
        $roleNotActiveSocial = new Role();
        $roleNotActiveSocial->setName('ROLE_NOTACTIVE_SOCIAL');
        $manager->persist($roleNotActiveSocial);

        // create the ROLE_NOTACTIVE_COMPANY role
        $roleNotActiveCompany = new Role();
        $roleNotActiveCompany->setName('ROLE_NOTACTIVE_COMPANY');
        $manager->persist($roleNotActiveCompany);

        // create the ROLE_COMPANY role
        $roleCompany = new Role();
        $roleCompany->setName('ROLE_COMPANY');
        $manager->persist($roleCompany);

        // create the ROLE_ADMIN role
        $roleAdmin = new Role();
        $roleAdmin->setName('ROLE_ADMIN');
        $manager->persist($roleAdmin);

        // create the ROLE_NOTACTIVE role
        $roleNotActive = new Role();
        $roleNotActive->setName('ROLE_NOTACTIVE');
        $manager->persist($roleNotActive);

        // create the ROLE_USER role
        $roleUser = new Role();
        $roleUser->setName('ROLE_USER');
        $manager->persist($roleUser);

        // create the ROLE_UPDATABLE_USERNAME role
        $roleUserName = new Role();
        $roleUserName->setName('ROLE_UPDATABLE_USERNAME');
        $manager->persist($roleUserName);

        // create the ROLE_COMPANY_MANAGER role
        $roleUserName = new Role();
        $roleUserName->setName('ROLE_COMPANY_MANAGER');
        $manager->persist($roleUserName);

        // create admin user
        $user = new User();
        $user->setFirstName('Objects');
        $user->setLastName('Objects');
        $user->setLoginName('Objects');
        $user->setUserPassword('0bjects123');
        $user->setEmail('objects@objects.ws');
        $user->setGender(1);
        $user->setCountry('EG');
        $user->setCity('AI Iskandariyah');
        $user->setAddress('later');
        $user->getUserRoles()->add($roleAdmin);
        $manager->persist($user);

        // create admin user
        $user1 = new User();
        $user1->setFirstName('mahmoud');
        $user1->setLastName('mahmoud');
        $user1->setLoginName('mahmoud');
        $user1->setUserPassword('123');
        $user1->setEmail('mahmoud@objects.ws');
        $user1->setGender(1);
        $user1->setCountry('EG');
        $user1->setCity('AI Iskandariyah');
        $user1->setAddress('later');
        $user1->getUserRoles()->add($roleAdmin);
        $manager->persist($user1);

        // create active user
        $user2 = new User();
        $user2->setFirstName('Ahmed');
        $user2->setLastName('Ahmed');
        $user2->setLoginName('Ahmed');
        $user2->setUserPassword('123');
        $user2->setEmail('ahmed@objects.ws');
        $user2->setGender(1);
        $user2->setCountry('EG');
        $user2->setCity('AI Iskandariyah');
        $user2->setAddress('later');
        $user2->getUserRoles()->add($roleUser);
        $manager->persist($user2);


        //create a user
        $user3 = new User();
        $user3->setFirstName('mirehan');
        $user3->setLastName('mirehan');
        $user3->setLoginName('mirehan');
        $user3->setUserPassword('123');
        $user3->setEmail('mirehan@objects.ws');
        $user3->setGender(0);
        $user3->setCountry('EG');
        $user3->setCity('AI Iskandariyah');
        $user3->setAddress('later');
        $user3->getUserRoles()->add($roleUser);
        $user3->getUserRoles()->add($roleUserName);
        $manager->persist($user3);

        //create a NotActivated user
        $user4 = new User();
        $user4->setFirstName('notactive');
        $user4->setLastName('notactive');
        $user4->setLoginName('notactive');
        $user4->setUserPassword('123');
        $user4->setEmail('notactive@objects.ws');
        $user4->setGender(0);
        $user4->setCountry('EG');
        $user4->setCity('AI Iskandariyah');
        $user4->setAddress('later');
        $user4->getUserRoles()->add($roleNotActive);
        $manager->persist($user4);

        $manager->flush();
    }

}