<?php

declare(strict_types=1);

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Testwork\Hook\Scope\AfterSuiteScope;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Doctrine\DBAL\Driver\PDOSqlite\Driver as SqliteDriver;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\WebServerBundle\Command\ServerStartCommand;
use Symfony\Bundle\WebServerBundle\Command\ServerStopCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements KernelAwareContext
{
    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
    }

    public function setKernel(KernelInterface $kernelInterface)
    {
        $this->kernel = $kernelInterface;
    }

    public function getKernel(): KernelInterface
    {
        return $this->kernel;
    }

    /** @BeforeSuite */
    public static function prepare(BeforeSuiteScope $scope)
    {
        $commandTester = new CommandTester(new ServerStartCommand());
        $commandTester->execute([]);
    }

    /** @AfterSuite */
    public static function teardown(AfterSuiteScope $scope)
    {
        $commandTester = new CommandTester(new ServerStopCommand());
        $commandTester->execute([]);
    }

    /** @BeforeScenario */
    public function before(BeforeScenarioScope $scope)
    {
        $this->buildAllSchema();
    }

    public function buildAllSchema()
    {
        $container = $this->getKernel()->getContainer();

        $entityManagers = $container->get('doctrine')->getManagers();

        /** @var EntityManager $em */
        foreach ($entityManagers as $name => $em) {
            $metadata = $em->getMetadataFactory()->getAllMetadata();
            if (!empty($metadata)) {
                if ($this->hasDriver($em, SqliteDriver::class)) {
                    $backupPath = $this->createBackupName($metadata);
                    $actualPath = $em->getConnection()->getParams()['path'];

                    if (file_exists($backupPath)) {
                        $this->backupDatabase($backupPath, $actualPath);

                        return;
                    }

                    $this->buildSingleSchema($em, $metadata);
                    $this->backupDatabase($actualPath, $backupPath);
                }

                $this->buildSingleSchema($em, $metadata);
            }
        }
    }

    protected function createBackupName($metadata)
    {
        return $this->getKernel()->getCacheDir().'/test_'.md5(serialize($metadata)).'.db';
    }

    protected function buildSingleSchema(EntityManager $em, $metadata)
    {
        $tool = new SchemaTool($em);
        $tool->dropSchema($metadata);
        $tool->createSchema($metadata);
    }

    protected function backupDatabase($source, $destination)
    {
        $permission = 0775;
        copy($source, $destination);
        chmod($source, $permission);
        chmod($destination, $permission);
    }

    protected function hasDriver(EntityManager $em, $driverClass)
    {
        $conn = $em->getConnection();

        return $conn->getDriver() instanceof $driverClass;
    }
}
