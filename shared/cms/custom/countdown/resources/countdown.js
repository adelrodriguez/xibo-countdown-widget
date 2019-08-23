// Obtain final date from items object
var finalDate = items.finalDate;

var labels = ['weeks', 'days', 'hours', 'minutes', 'seconds'];
var template = _.template($('#countdown-template').html());
var currentDate = '00:00:00:00:00';
var nextDate = '00:00:00:00:00';
var parser = /([0-9]{2})/gi;
var $countdown = $('#countdown');

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
  var newDate = event.strftime('%w:%d:%H:%M:%S');
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
      var selector = '.%s'.replace(/%s/, label);
      var $node = $countdown.find(selector);

      // Update the node
      $node.removeClass('flip');
      $node.find('.curr').text(data.curr[label]);
      $node.find('.next').text(data.next[label]);

      // Wait for a repaint to then flip
      _.delay(
        function($node) {
          $node.addClass('flip');
        },
        50,
        $node
      );
    });
  }
});
