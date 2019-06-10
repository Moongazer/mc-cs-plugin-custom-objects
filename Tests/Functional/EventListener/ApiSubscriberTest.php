<?php

declare(strict_types=1);

/*
 * @copyright   2019 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc
 *
 * @link        https://mautic.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\CustomObjectsBundle\Tests\Functional\EventListener;

use MauticPlugin\CustomObjectsBundle\Tests\Functional\DataFixtures\Traits\CustomObjectsTrait;
use Mautic\CoreBundle\Test\MauticMysqlTestCase;
use FOS\RestBundle\Util\Codes;
use Mautic\LeadBundle\Model\LeadModel;

class ApiSubscriberTest extends MauticMysqlTestCase
{
    use CustomObjectsTrait;

    /**
     * A custom object with alias "unicorn" does not exist.
     * In this case no contact and no custom item should be created.
     */
    public function testCreatingContactWithCustomItemsWithUnexistingCustomObject(): void
    {
        $contact = [
            'email'         => 'contact1@api.test',
            'customObjects' => [
                'unicorn' => [
                    [
                        'name' => 'Custom Item Created Via Contact API',
                    ],
                ],
            ],
        ];

        $this->client->request('POST', 'api/contacts/new', $contact);

        $response     = $this->client->getResponse();
        $responseData = json_decode($response->getContent(), true);

        $this->assertSame(Codes::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertSame('Custom Object with alias = unicorn was not found', $responseData['errors'][0]['message']);

        /** @var CustomItemRepository $customItemRepository */
        $customItemRepository = $this->container->get('custom_item.repository');

        /** @var LeadModel $contactModel */
        $contactModel = $this->container->get('mautic.lead.model.lead');

        $this->assertNull($customItemRepository->findOneBy(['name' => 'Custom Item Created Via Contact API 2']));
        $this->assertNull($contactModel->getRepository()->findOneBy(['email' => 'contact1@api.test']));
    }

    public function testCreatingContactWithCustomItems(): void
    {
        $customObject = $this->createCustomObjectWithAllFields($this->container, 'Product');

        $contact = [
            'email'         => 'contact1@api.test',
            'customObjects' => [
                $customObject->getAlias() => [
                    [
                        'name'       => 'Custom Item Created Via Contact API 2',
                        'attributes' => [
                            'text-test-field'         => 'Yellow snake',
                            'textarea-test-field'     => "Multi\nline\nvalue",
                            'url-test-field'          => 'https://mautic.org',
                            'multiselect-test-field'  => ['option_b'],
                            'select-test-field'       => 'option_a',
                            'radio-group-test-field'  => 'option_b',
                            'phone-number-test-field' => '+420775308002',
                            'number-test-field'       => 123,
                            'hidden-test-field'       => 'secret',
                            'e-mail-test-field'       => 'john@doe.email',
                        ],
                    ],
                ],
            ],
        ];

        $this->client->request('POST', 'api/contacts/new', $contact);
        $response     = $this->client->getResponse();
        $responseData = json_decode($response->getContent(), true);

        $this->assertSame(Codes::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame(1, $responseData['contact']['id']);
        $this->assertSame('contact1@api.test', $responseData['contact']['fields']['all']['email']);
        $this->assertNull($responseData['contact']['fields']['all']['firstname']);
        $this->assertCount(1, $responseData['contact']['customObjects']);

        $customItemFromResponse = $responseData['contact']['customObjects'][$customObject->getAlias()]['data'][1];
        $this->assertSame(1, $customItemFromResponse['id']);
        $this->assertSame('Custom Item Created Via Contact API 2', $customItemFromResponse['name']);
        $this->assertSame('Yellow snake', $customItemFromResponse['attributes']['text-test-field']);
        $this->assertSame("Multi\nline\nvalue", $customItemFromResponse['attributes']['textarea-test-field']);
        $this->assertSame('https://mautic.org', $customItemFromResponse['attributes']['url-test-field']);
        $this->assertSame(['option_b'], $customItemFromResponse['attributes']['multiselect-test-field']);
        $this->assertSame('option_a', $customItemFromResponse['attributes']['select-test-field']);
        $this->assertSame('option_b', $customItemFromResponse['attributes']['radio-group-test-field']);
        $this->assertSame('+420775308002', $customItemFromResponse['attributes']['phone-number-test-field']);
        $this->assertSame(123, $customItemFromResponse['attributes']['number-test-field']);
        $this->assertSame('secret', $customItemFromResponse['attributes']['hidden-test-field']);
        $this->assertSame('john@doe.email', $customItemFromResponse['attributes']['e-mail-test-field']);

        // Let's try to update the contact and the custom item.

        $contact = [
            'email'         => 'contact1@api.test',
            'firstname'     => 'Contact1',
            'customObjects' => [
                $customObject->getAlias() => [
                    [
                        'id'         => 1,
                        'name'       => 'Custom Item Modified Via Contact API 2',
                        'attributes' => [
                            'text-test-field'         => 'Yellow cake',
                            'textarea-test-field'     => "Multi\nnine\nvalue",
                            'url-test-field'          => 'https://mautic.com',
                            'multiselect-test-field'  => ['option_a'],
                            'select-test-field'       => 'option_b',
                            'radio-group-test-field'  => 'option_a',
                            'phone-number-test-field' => '+420775308003',
                            'number-test-field'       => 123456,
                            'hidden-test-field'       => 'secret sauce',
                            'e-mail-test-field'       => 'john@doe.com',
                        ],
                    ],
                ],
            ],
        ];

        $this->client->request('POST', 'new', $contact); // For some reason the api/contacts path is stored in the client.
        $response     = $this->client->getResponse();
        $responseData = json_decode($response->getContent(), true);
        // dump($responseData/*, $this->client->getRequest()*/);die;
        $this->assertSame(Codes::HTTP_OK, $response->getStatusCode());
        $this->assertSame(1, $responseData['contact']['id']);
        $this->assertSame('contact1@api.test', $responseData['contact']['fields']['all']['email']);
        $this->assertSame('Contact1', $responseData['contact']['fields']['all']['firstname']);
        $this->assertCount(1, $responseData['contact']['customObjects'][$customObject->getAlias()]['data']);

        $customItemFromResponse = $responseData['contact']['customObjects'][$customObject->getAlias()]['data'][1];
        $this->assertSame(1, $customItemFromResponse['id']);
        $this->assertSame('Custom Item Modified Via Contact API 2', $customItemFromResponse['name']);
        $this->assertSame('Yellow cake', $customItemFromResponse['attributes']['text-test-field']);
        $this->assertSame("Multi\nnine\nvalue", $customItemFromResponse['attributes']['textarea-test-field']);
        $this->assertSame('https://mautic.com', $customItemFromResponse['attributes']['url-test-field']);
        $this->assertSame(['option_a'], $customItemFromResponse['attributes']['multiselect-test-field']);
        $this->assertSame('option_b', $customItemFromResponse['attributes']['select-test-field']);
        $this->assertSame('option_a', $customItemFromResponse['attributes']['radio-group-test-field']);
        $this->assertSame('+420775308003', $customItemFromResponse['attributes']['phone-number-test-field']);
        $this->assertSame(123456, $customItemFromResponse['attributes']['number-test-field']);
        $this->assertSame('secret sauce', $customItemFromResponse['attributes']['hidden-test-field']);
        $this->assertSame('john@doe.com', $customItemFromResponse['attributes']['e-mail-test-field']);
    }
}
