<template>
  <div ref="timeline" class="timeline"></div>
</template>

<script>
import { Timeline, DataSet } from "vis/index-timeline-graph2d";
import "vis/dist/vis-timeline-graph2d.min.css";
import {debounce} from 'lodash';

export default {
  props: {
    items: {
      type: [Array, DataSet],
      default: () => []
    },
    initStart: {
      type: [Date],
      default: () => new Date()
    },
    editable: {
      type: [Boolean],
      default: false
    }
  },

  data() {
    return {
      dataset: null
    };
  },

  watch: {
    items: {
      handler: function(newItems) {
        this.dataset.clear();
        this.dataset.add(newItems);
        // this.dataset.update(newItems);
      },
      deep: true
    },
    editable: {
      handler: function(newValue) {
        this.timeline.setOptions({
          selectable: newValue,
          editable: {
            updateTime: newValue
          }
        });
      }
    }
  },

  methods: {
    buildButtons(element, buttonName, timelineItem) {
      const button = document.createElement("button");
      button.classList += `vis-item__btn vis-item__btn--${buttonName}`;
      button.dataset.itemId = timelineItem.id;
      button.innerHTML = timelineItem.buttons[buttonName].label;
      button.onclick = timelineItem.buttons[buttonName].handler;
      button.addEventListener('touchstart', function() {
        timelineItem.buttons[buttonName].handler({currentTarget: this});
      });
      element.insertAdjacentElement('afterend', button);
      return element;
    }
  },

  mounted() {
    let onMoved = debounce(function(item) {
      this.$emit('itemmoved', item);
    }, 500).bind(this);

    let options = {
      start: this.initStart,
      orientation: {axis: 'top'},
      format: {
        minorLabels: {
          weekday: 'ddd Do',
          day: 'Do'
        }
      },
      selectable: this.editable,
      editable: {
        updateTime: this.editable,
        overrideItems: true
      },
      onMoving: function(item, callback) {
        callback(item);
        onMoved(item);
      }
    }

    options.template = (item, element) => {
      element.innerHTML = item.content;
      ((touchTreshold = 250) => {
        let touchStart, target;

        element.addEventListener('touchstart', e => {
          touchStart = new Date();
          target = e.targetTouches[0].target;
        });

        element.addEventListener('touchend', e => {
          const diff = new Date() - touchStart;
          if ((diff < touchTreshold) && target.href) {
            window.location = target.href;
          }
        })
      })()


      if (item.buttons && Object.keys(item.buttons).length > 0) {
        Object.keys(item.buttons).reduce((element, buttonName) => this.buildButtons(element, buttonName, item), element);
      }
    };

    this.dataset = Array.isArray(this.items) ? new DataSet(this.items) : this.items;

    this.timeline = new Timeline(
      this.$refs.timeline,
      this.dataset,
      options
    );

    this.timeline.on('rangechanged', props => this.$emit('onRangeChanged', props));
  }
};
</script>

<style>
.vis-major {
  font-size: 1.5rem;
}

.vis-timeline {
  border: none;
}

.vis-panel {
  border: none !important;
}

.vis-minor {
  border: none !important;
}

.vis-item {
  background: #EBEBEB;
  border: none !important;
  border-radius: 20px !important;
  z-index: auto !important;
}

.vis-current-time {
  width: 3px;
  background: #FF5858;
  border-radius: 1px;
  margin-top: 45px;
  z-index: 100;
}

.vis-item-content {
  padding-left: 15px !important;
  overflow: hidden !important;
  display: block !important;
}

.vis-item-overflow {
  overflow: visible !important;
}

.vis-item__btn {
  position: absolute;
  right: 0;
  top: 50%;
  transform: translate(50%, -50%);
  z-index: 101;
  cursor: pointer;
  border: none;
  border-radius: 50%;
  background: white;
  width: 29px;
  height: 29px;
}

.vis-item__btn::after {
  content: '';
  width: 25px;
  height: 25px;
  /* background: rgb(91, 194, 155); */
  border-radius: 50%;
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
}

.vis-item__btn > * {
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
  z-index: 99;
}
</style>
