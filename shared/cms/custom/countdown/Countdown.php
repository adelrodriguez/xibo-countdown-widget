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
        $items = array(
            'finalDate' => $this->getOption('finalDate', '2020/01/01')
        );

        $this
            ->initialiseGetResource()
            ->appendViewPortWidth($this->region->width)
            ->appendJavaScriptFile('vendor/jquery-1.11.1.min.js')
            ->appendJavaScriptFile('jquery.countdown.min.js')
            ->appendJavaScriptFile('lodash.min.js')
            ->appendCssFile('countdown.css')
            ->appendBody('
                <link href="https://fonts.googleapis.com/css?family=Oswald|Source+Sans+Pro&display=swap" rel="stylesheet">
                <div id="countdown"></div>
                <script type="text/template" id="countdown-template">
                <div class="time <%= label %>">
                    <span class="count curr top"><%= curr %></span>
                    <span class="count next top"><%= next %></span>
                    <span class="count next bottom"><%= next %></span>
                    <span class="count curr bottom"><%= curr %></span>
                    <span class="label"><%= label.length < 6 ? label : label.substr(0, 3)  %></span>
                </div>
                </script>
            ')
            ->appendItems($items)
            ->appendJavaScriptFile('countdown.js');

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
