<?php
/*
 * Spring Signage Ltd - http://www.springsignage.com
 * Copyright (C) 2016 Spring Signage Ltd
 * (Audio.php)
 */
namespace Xibo\Custom\Countdown;

class Countdown extends \Xibo\Widget\ModuleWidget
{
    protected $codeSchemaVersion = 0.1;

    public function installOrUpdate($moduleFactory)
    {
        if ($this->module == null) {
            // Install
            $module = $moduleFactory->createEmpty();
            $module->name = 'Countdown';
            $module->type = 'countdown';
            $module->class = 'Xibo\\Custom\\Countdown\\Countdown';
            $module->description = 'A module for displaying a countdown';
            $module->enabled = 1;
            $module->previewEnabled = 1;
            $module->assignable = 1;
            $module->regionSpecific = 1;
            $module->renderAs = 'html';
            $module->schemaVersion = $this->codeSchemaVersion;
            $module->defaultDuration = 60;
            $module->settings = [];
            $module->viewPath = '../custom/countdown';

            // Set the newly created module and then call install
            $this->setModule($module);
            $this->installModule();
        }

        // Install and additional module files that are required.
        $this->installFiles();
    }

    /**
     * Install Files
     */
    public function installFiles()
    {
        $this->mediaFactory->createModuleSystemFile(PROJECT_ROOT . '/modules/vendor/jquery-1.11.1.min.js')->save();
        $this->mediaFactory->createModuleSystemFile(PROJECT_ROOT . '/modules/vendor/moment.js')->save();

        // Install resource files
        $folder = PROJECT_ROOT . '/custom/countdown/resources';
        foreach ($this->mediaFactory->createModuleFileFromFolder($folder) as $media) {
            /* @var Media $media */
            $media->save();
        }
    }

    /**
     * JavaScript functions for the layout designer
     */
    public function layoutDesignerJavaScript()
    {
        return 'countdown-designer-javascript';
    }

    /**
     * Get Resource
     * @param int $displayId
     * @return mixed
     */
    public function getResource($displayId = 0)
    {
        $finalDate = $this->getOption('finalDate', '2020/01/01');

        $this
            ->initialiseGetResource()
            ->appendViewPortWidth($this->region->width)
            ->appendJavaScriptFile('jquery-1.11.1.min.js')
            ->appendJavaScriptFile('jquery.countdown.min.js')
            ->appendBody('<div id="countdown"></div>')
            ->appendCssFile('countdown.css')
            ->appendJavaScript('
                $("#countdown").countdown("' . $finalDate . '", function(event) {
                    $(this).html(event.strftime(""
                        + "<div><span class=\"unit\">%w</span><span>weeks</span></div>"
                        + "<div><span class=\"unit\">%d</span><span>days</span></div>"
                        + "<div><span class=\"unit\">%H</span><span>hours</span></div>"
                        + "<div><span class=\"unit\">%M</span><span>min</span></div>"
                        + "<div><span class=\"unit\">%S</span><span>sec</span></div>"
                    ));
                });
            ');
            

        return $this->finaliseGetResource();
    }

    public function edit()
    {
        $this->setOption('name', $this->getSanitizer()->getString('name'));
        $this->setUseDuration($this->getSanitizer()->getCheckbox('useDuration'));
        $this->setDuration($this->getSanitizer()->getInt('duration', $this->getDuration()));
        $this->setOption('finalDate', $this->getSanitizer()->getString('finalDate'));

        $this->isValid();

        // Save the widget
        $this->saveWidget();
    }

    /** @inheritdoc */
    public function isValid()
    {
        if ($this->getUseDuration() == 1 && $this->getDuration() == 0)
            throw new InvalidArgumentException(__('Please enter a duration'), 'duration');

        return self::$STATUS_VALID;
    }
}
