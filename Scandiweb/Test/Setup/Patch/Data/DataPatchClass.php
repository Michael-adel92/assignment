<?PHP
declare(strict_types=1);

namespace Scandiweb\Test\Setup\Patch\Data;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;

class DataPatchClass implements DataPatchInterface
{
    /**
     * @var ProductInterfaceFactory
     */
    protected $productFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var SourceItemInterface
     */
    protected $sourceItem;

    /**
     * @var CategoryLinkManagementInterface
     */
    protected $categoryLink;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @param ProductInterfaceFactory $productFactory
     * @param ProductRepositoryInterface $productRepository
     * @param State $state
     * @param SourceItemInterface $sourceItem
     * @param CategoryLinkManagementInterface $categoryLink
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(
        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        State $state,
        SourceItemInterface $sourceItem,
        CategoryLinkManagementInterface $categoryLink,
        StockRegistryInterface $stockRegistry
    ) {
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->state = $state;
        $this->sourceItem = $sourceItem;
        $this->categoryLink = $categoryLink;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * @return $this
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws LocalizedException
     * @throws StateException
     */
    public function execute()
    {
        /** @var ProductInterface $product */
        $product = $this->productFactory->create();
        $product->setSku('SAMPLE-ITEM')
            ->setName('Sample Item')
            ->setTypeId(Type::TYPE_SIMPLE)
            ->setVisibility(4)
            ->setPrice(100)
            ->setAttributeSetId(4)
            ->setStatus(Status::STATUS_ENABLED);
        $this->sourceItem->setStatus(SourceItemInterface::STATUS_IN_STOCK);
        $this->productRepository->save($product);

        $stockItem = $this->stockRegistry->getStockItemBySku($product->getSku());
        $stockItem->setIsInStock(1);
        $stockItem->setQty(20);
        $this->stockRegistry->updateStockItemBySku($product->getSku(), $stockItem);

        $this->categoryLink->assignProductToCategories($product->getSku(), [2]);

        return $this;
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function apply(): void
    {
        $this->state->setAreaCode(Area::AREA_ADMINHTML);
        $this->state->emulateAreaCode(Area::AREA_ADMINHTML, [$this, 'execute']);
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}