<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Order;

use \Magento\Sales\Model\Order\CreditmemoRepository;

/**
 * Class CreditmemoRepositoryTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreditmemoRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CreditmemoRepository
     */
    protected $creditmemo;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Metadata|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataMock;

    /**
     * @var \Magento\Sales\Api\Data\CreditmemoSearchResultInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultFactoryMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->metadataMock = $this->getMock(\Magento\Sales\Model\ResourceModel\Metadata::class, [], [], '', false);
        $this->searchResultFactoryMock = $this->getMock(
            \Magento\Sales\Api\Data\CreditmemoSearchResultInterfaceFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->creditmemo = $objectManager->getObject(
            \Magento\Sales\Model\Order\CreditmemoRepository::class,
            [
                'metadata' => $this->metadataMock,
                'searchResultFactory' => $this->searchResultFactoryMock
            ]
        );
        $this->type = $this->getMock(\Magento\Eav\Model\Entity\Type::class, ['fetchNewIncrementId'], [], '', false);
    }

    public function testGet()
    {
        $id = 1;
        $entity = $this->getMockBuilder(\Magento\Sales\Model\Order\Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entity->expects($this->once())
            ->method('load')
            ->with($id)
            ->willReturn($entity);
        $entity->expects($this->once())
            ->method('getEntityId')
            ->willReturn($id);

        $this->metadataMock->expects($this->once())
            ->method('getNewInstance')
            ->willReturn($entity);

        $this->assertEquals($entity, $this->creditmemo->get($id));
        //retrieve entity from registry
        $this->assertEquals($entity, $this->creditmemo->get($id));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Id required
     */
    public function testGetNoId()
    {
        $this->creditmemo->get(null);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Requested entity doesn't exist
     */
    public function testGetEntityNoId()
    {
        $id = 1;
        $entity = $this->getMockBuilder(\Magento\Sales\Model\Order\Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entity->expects($this->once())
            ->method('load')
            ->with($id)
            ->willReturn($entity);
        $entity->expects($this->once())
            ->method('getEntityId')
            ->willReturn(null);

        $this->metadataMock->expects($this->once())
            ->method('getNewInstance')
            ->willReturn($entity);

        $this->assertNull($entity, $this->creditmemo->get($id));
    }

    public function testCreate()
    {
        $entity = $this->getMockBuilder(\Magento\Sales\Model\Order\Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataMock->expects($this->once())
            ->method('getNewInstance')
            ->willReturn($entity);
        $this->assertEquals($entity, $this->creditmemo->create());
    }

    public function testGetList()
    {
        $filterGroup = $this->getMockBuilder(\Magento\Framework\Api\Search\FilterGroup::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filterGroups = [$filterGroup];
        $field = 'test_field';
        $fieldValue = 'test_value';

        $filter = $this->getMockBuilder(\Magento\Framework\Api\Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filter->expects($this->any())
            ->method('getConditionType')
            ->willReturn(false);
        $filter->expects($this->any())
            ->method('getField')
            ->willReturn($field);
        $filter->expects($this->any())
            ->method('getValue')
            ->willReturn($fieldValue);

        $filterGroup->expects($this->once())
            ->method('getFilters')
            ->willReturn([$filter]);

        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchCriteria->expects($this->once())
            ->method('getFilterGroups')
            ->willReturn($filterGroups);

        $collection = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Creditmemo\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->once())
            ->method('addFieldToFilter')
            ->withAnyParameters();

        $this->searchResultFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collection);

        $this->assertEquals($collection, $this->creditmemo->getList($searchCriteria));
    }

    public function testDelete()
    {
        $entity = $this->getMockBuilder(\Magento\Sales\Model\Order\Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entity->expects($this->once())
            ->method('getEntityId')
            ->willReturn(1);

        $mapper = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mapper->expects($this->once())
            ->method('delete')
            ->with($entity);

        $this->metadataMock->expects($this->any())
            ->method('getMapper')
            ->willReturn($mapper);

        $this->assertTrue($this->creditmemo->delete($entity));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotDeleteException
     * @expectedExceptionMessage Could not delete credit memo
     */
    public function testDeleteWithException()
    {
        $entity = $this->getMockBuilder(\Magento\Sales\Model\Order\Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entity->expects($this->never())
            ->method('getEntityId');

        $mapper = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mapper->expects($this->once())
            ->method('delete')
            ->willThrowException(new \Exception('error'));

        $this->metadataMock->expects($this->any())
            ->method('getMapper')
            ->willReturn($mapper);

        $this->creditmemo->delete($entity);
    }

    public function testSave()
    {
        $entity = $this->getMockBuilder(\Magento\Sales\Model\Order\Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entity->expects($this->any())
            ->method('getEntityId')
            ->willReturn(1);

        $mapper = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mapper->expects($this->once())
            ->method('save')
            ->with($entity);

        $this->metadataMock->expects($this->any())
            ->method('getMapper')
            ->willReturn($mapper);

        $this->assertEquals($entity, $this->creditmemo->save($entity));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Could not save credit memo
     */
    public function testSaveWithException()
    {
        $entity = $this->getMockBuilder(\Magento\Sales\Model\Order\Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entity->expects($this->never())
            ->method('getEntityId');

        $mapper = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mapper->expects($this->once())
            ->method('save')
            ->willThrowException(new \Exception('error'));

        $this->metadataMock->expects($this->any())
            ->method('getMapper')
            ->willReturn($mapper);

        $this->assertEquals($entity, $this->creditmemo->save($entity));
    }
}
