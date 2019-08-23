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
        $finalDate = $this->getOption('finalDate', '2020/01/01');

        $this
            ->initialiseGetResource()
            ->appendViewPortWidth($this->region->width)
            ->appendJavaScriptFile('vendor/jquery-1.11.1.min.js')
            ->appendJavaScriptFile('jquery.countdown.min.js')
            ->appendJavaScriptFile('lodash.min.js')
            ->appendCssFile('countdown.css')
            ->appendBody('.
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
            ->appendJavaScript('
                var labels = ["weeks", "days", "hours", "minutes", "seconds"];
                var finalDate = "'. $finalDate .'";
                var template = _.template($("#countdown-template").html());
                var currentDate = "00:00:00:00:00";
                var nextDate = "00:00:00:00:00";
                var parser = /([0-9]{2})/gi;
                var $countdown = $("#countdown");
                
                // Parse countdown string to an object
                function strfobj(str) {
                var parsed = str.match(parser);
                var obj = {};
                
                labels.forEach((label, i) => {
                    obj[label] = parsed[i];
                });
                
                return obj;
                }
                
                // Return the time components that diffs
                function diff(obj1, obj2) {
                var diff = [];
                
                labels.forEach(function(key) {
                    if (obj1[key] !== obj2[key]) {
                    diff.push(key);
                    }
                });
                
                return diff;
                }
                
                // Build the layout
                var initialData = strfobj(currentDate);
                
                labels.forEach(function(label, i) {
                $countdown.append(
                    template({
                    curr: initialData[label],
                    next: initialData[label],
                    label: label,
                    })
                );
                });
                
                // Starts the countdown
                $countdown.countdown(finalDate, function(event) {
                var newDate = event.strftime("%w:%d:%H:%M:%S");
                var data;
                
                if (newDate !== nextDate) {
                    currentDate = nextDate;
                    nextDate = newDate;
                
                    // Setup the data
                    data = {
                    curr: strfobj(currentDate),
                    next: strfobj(nextDate),
                    };
                
                    // Apply the new values to each node that changed
                    diff(data.curr, data.next).forEach(function(label) {
                    var selector = ".%s".replace(/%s/, label);
                    var $node = $countdown.find(selector);
                    
                    // Update the node
                    $node.removeClass("flip");
                    $node.find(".curr").text(data.curr[label]);
                    $node.find(".next").text(data.next[label]);
                    
                    // Wait for a repaint to then flip
                    _.delay(
                        function($node) {
                        $node.addClass("flip");
                        },
                        50,
                        $node
                    );
                    });
                }
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
