<?php
/**
 * Passbolt ~ Open source password manager for teams
 * Copyright (c) Passbolt SARL (https://www.passbolt.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Passbolt SARL (https://www.passbolt.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.passbolt.com Passbolt(tm)
 * @since         2.0.0
 */

namespace App\Test\TestCase\Controller\Groups;

use App\Test\TestCase\ApplicationTest;
use App\Utility\Common;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

class GroupsIndexControllerTest extends ApplicationTest
{
    public $fixtures = ['app.users', 'app.groups', 'app.groups_users', 'app.permissions'];

    public function testIndexSuccess()
    {
        $this->authenticateAs('ada');
        $this->getJson('/groups.json?api-version=2');
        $this->assertSuccess();
        $this->assertGreaterThan(1, count($this->_responseJsonBody));

        // Expected content.
        $this->assertGroupAttributes($this->_responseJsonBody[0]);
        // Not expected content.
        $this->assertObjectNotHasAttribute('modifier', $this->_responseJsonBody[0]);
        $this->assertObjectNotHasAttribute('users', $this->_responseJsonBody[0]);
    }

    public function testIndexApiV1Success()
    {
        $this->authenticateAs('ada');
        $this->getJson('/groups.json');
        $this->assertSuccess();
        $this->assertGreaterThan(1, count($this->_responseJsonBody));

        // Expected fields.
        $this->assertObjectHasAttribute('Group', $this->_responseJsonBody[0]);
        $this->assertGroupAttributes($this->_responseJsonBody[0]->Group);
        // Not expected fields.
        $this->assertObjectNotHasAttribute('Modifier', $this->_responseJsonBody[0]);
        $this->assertObjectNotHasAttribute('User', $this->_responseJsonBody[0]);
    }

    public function testIndexContainSuccess()
    {
        $this->authenticateAs('ada');
        $urlParameter = 'contain[modifier]=1&contain[user]=1';
        $this->getJson("/groups.json?$urlParameter&api-version=2");
        $this->assertSuccess();
        $this->assertGreaterThan(1, count($this->_responseJsonBody));

        // Expected content.
        $this->assertGroupAttributes($this->_responseJsonBody[0]);
        $this->assertObjectHasAttribute('modifier', $this->_responseJsonBody[0]);
        $this->assertUserAttributes($this->_responseJsonBody[0]->modifier);
        $this->assertObjectHasAttribute('users', $this->_responseJsonBody[0]);
        $this->assertUserAttributes($this->_responseJsonBody[0]->users[0]);
    }

    public function testIndexContainApiV1SSuccess()
    {
        $this->authenticateAs('ada');
        $urlParameter = 'contain[modifier]=1&contain[user]=1';
        $this->getJson("/groups.json?$urlParameter");
        $this->assertSuccess();
        $this->assertGreaterThan(1, count($this->_responseJsonBody));

        // Expected content.
        $this->assertObjectHasAttribute('Group', $this->_responseJsonBody[0]);
        $this->assertGroupAttributes($this->_responseJsonBody[0]->Group);
        $this->assertObjectHasAttribute('Modifier', $this->_responseJsonBody[0]);
        $this->assertUserAttributes($this->_responseJsonBody[0]->Modifier);
        $this->assertObjectHasAttribute('User', $this->_responseJsonBody[0]);
        $this->assertUserAttributes($this->_responseJsonBody[0]->User[0]);
    }

    public function testIndexFilterHasUsersSuccess()
    {
        $this->authenticateAs('ada');
        $urlParameter = 'filter[has-users]=' . Common::uuid('user.id.irene');
        $this->getJson("/groups.json?$urlParameter&api-version=2");
        $this->assertSuccess();
        $this->assertCount(3, $this->_responseJsonBody);
        $groupsIds = Hash::extract($this->_responseJsonBody, '{n}.id');
        $expectedGroupsIds = [Common::uuid('group.id.creative'), Common::uuid('group.id.developer'), Common::uuid('group.id.ergonom')];
        $this->assertEquals(0, count(array_diff($expectedGroupsIds, $groupsIds)));
    }

    public function testIndexFilterHasManagersSuccess()
    {
        $this->authenticateAs('ada');
        $urlParameter = 'filter[has-managers]=' . Common::uuid('user.id.ping');
        $this->getJson("/groups.json?$urlParameter&api-version=2");
        $this->assertSuccess();
        $this->assertCount(2, $this->_responseJsonBody);
        $groupsIds = Hash::extract($this->_responseJsonBody, '{n}.id');
        $expectedGroupsIds = [Common::uuid('group.id.human_resource'), Common::uuid('group.id.it_support')];
        $this->assertEquals(0, count(array_diff($expectedGroupsIds, $groupsIds)));
    }

    public function testIndexErrorNotAuthenticated()
    {
        $this->getJson('/groups.json');
        $this->assertAuthenticationError();
    }
}