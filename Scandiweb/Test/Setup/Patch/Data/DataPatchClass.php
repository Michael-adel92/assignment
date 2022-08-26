<?PHP
declare(strict_types=1);

namespace Scandiweb\Test\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\State;

class DataPatchClass implements DataPatchInterface
{
    public $productFactory;
    public $productRepository;
    public $state;

    /**
     * @param ProductInterfaceFactory $productFactory
     * @param ProductRepositoryInterface $productRepository
     * @param State $state
     */
    public function __construct(
        ProductInterfaceFactory    $productFactory,
        ProductRepositoryInterface $productRepository,
        State                      $state
    )
    {
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->state = $state;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);

        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $this->productFactory->create();
        $product->setSku('SAMPLE-ITEM')
            ->setName('Sample Item')
            ->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
            ->setVisibility(4)
            ->setPrice(1)
            ->setAttributeSetId(4)
            ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $product->setCategoryIds([2]);

        $this->productRepository->save($product);
        return $this;
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
