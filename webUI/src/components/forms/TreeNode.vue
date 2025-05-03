<template>
    <div class="tree-node" :class="{ 'has-children': node.children && node.children.length > 0 }">
      <div 
        class="node-content"
        :class="{
          'selected': node.code === selectedCode,
          'has-children': node.children && node.children.length > 0,
          'not-selectable': node.children && node.children.length > 0
        }"
        @click="handleClick"
      >
        <v-icon
          v-if="node.children && node.children.length > 0"
          @click.stop="toggleNode"
          :class="{ 'rotated': node.isOpen }"
        >
          mdi-chevron-right
        </v-icon>
        <div class="tree-node-icon">
          <v-icon v-if="node.children && node.children.length > 0">mdi-folder</v-icon>
          <v-icon v-else>mdi-file-document</v-icon>
        </div>
        <div class="tree-node-label" :class="{ 'selected': node.code === selectedCode }">
          {{ node.name }}
        </div>
      </div>
      <div v-if="node.isOpen && node.children && node.children.length > 0" class="tree-children">
        <tree-node
          v-for="child in node.children"
          :key="child.code"
          :node="child"
          :selected-code="selectedCode"
          :selectable-only="selectableOnly"
          @select="$emit('select', $event)"
          @toggle="$emit('toggle', $event)"
        />
      </div>
    </div>
  </template>
  
  <script setup lang="ts">
  import { defineEmits } from 'vue';
  
  const props = defineProps({
    node: {
      type: Object,
      required: true,
    },
    selectedCode: {
      type: String,
      default: null,
    },
    selectableOnly: {
      type: Boolean,
      default: false,
    },
  });
  
  const emit = defineEmits(['select', 'toggle']);
  
  const handleClick = () => {
    emit('select', props.node);
  };
  
  const toggleNode = () => {
    emit('toggle', props.node);
  };
  </script>
  
  <style scoped>
  .tree-node {
    margin-left: 24px;
  }
  
  .node-content {
    display: flex;
    align-items: center;
    padding: 4px 8px;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.2s;
  }
  
  .node-content:hover {
    background-color: rgba(var(--v-theme-primary), 0.1);
  }
  
  .node-content.selected {
    background-color: var(--v-primary-base);
    color: white;
  }
  
  .node-content.not-selectable {
    opacity: 0.7;
    cursor: not-allowed;
  }
  
  .tree-node-icon {
    width: 24px;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-right: 8px;
  }
  
  .tree-node-label {
    flex: 1;
    font-size: 0.9rem;
    font-family: 'Vazir', sans-serif;
  }
  
  .tree-node-label.selected {
    color: rgb(var(--v-theme-primary));
    font-weight: 500;
  }
  
  .tree-children {
    margin-left: 24px;
    border-right: 2px solid rgba(var(--v-theme-primary), 0.1);
    padding-right: 8px;
  }
  
  .rotated {
    transform: rotate(90deg);
  }
  </style>