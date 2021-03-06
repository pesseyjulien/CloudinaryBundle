<?php

namespace Speicher210\CloudinaryBundle\Command;

use Speicher210\CloudinaryBundle\Cloudinary\Uploader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Command to upload to Cloudinary the images needed for the demo beacons.
 */
class UploadCommand extends Command
{
    /**
     * Cloudinary uploader.
     *
     * @var Uploader
     */
    private $uploader;

    public function __construct(Uploader $uploader)
    {
        $this->uploader = $uploader;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sp210:cloudinary:upload')
            ->setDescription(
                'Upload files from a directory to Cloudinary. The public ID will be the name of the file. It will overwrite the existing files automatically.'
            )
            ->addArgument('directory', InputArgument::REQUIRED, 'The directory from where to upload the files.')
            ->addOption(
                'prefix',
                null,
                InputOption::VALUE_REQUIRED,
                'Add prefix to uploaded files.'
            )
            ->addOption(
                'filter',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter to apply when scanning the directory (ex: "*.jpg").'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $files  = Finder::create()->files()->in($input->getArgument('directory'));
        $prefix = $input->getOption('prefix');
        if ($input->getOption('filter')) {
            $files->name($input->getOption('filter'));
        }

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            try {
                $fileName = $prefix . $file->getBasename('.' . $file->getExtension());
                $this->uploadFileToCloudinary($file, $fileName);

                $output->writeln(sprintf('Uploaded image <info>%s</info>', $file->getRealPath()));
            } catch (\Exception $e) {
                $output->writeln(
                    sprintf('An error has occurred while trying to upload image: %s', $e->getMessage())
                );
            }
        }
    }

    /**
     * Upload a picture to Cloudinary.
     *
     * @param SplFileInfo $file     The file to upload.
     * @param string      $publicId Path where to upload in Cloudinary.
     *
     * @return array
     */
    protected function uploadFileToCloudinary($file, $publicId)
    {
        $result = $this->uploader->upload(
            $file->getRealPath(),
            [
                'public_id' => $publicId,
            ]
        );

        return $result;
    }
}
