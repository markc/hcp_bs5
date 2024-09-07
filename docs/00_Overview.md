Here's a refactored and streamlined version of the documentation:

# NetServa HCP: Architecture Overview

## Core Concepts

1. **Single Entry Point (index.php)**
   - Acts as the front controller
   - Contains global configuration
   - Implements autoloading via spl_autoload_register()

2. **Global Object ($this->g)**
   - Stores configuration, input, and output data
   - $this->g->out accumulates content for final rendering

3. **Encapsulated Rendering**
   - Plugin and theme classes generate HTML content
   - Content is assigned to $this->g->out['main'] and other keys

4. **Flexible Output**
   - Init class __toString() method handles final rendering
   - Supports HTML, plain text, and JSON outputs
   - Enables easy API integration

## Key Features

- **Modular Structure**: Separate classes for different functionalities
- **Configuration Override**: Optional lib/.ht_conf.php for environment-specific settings
- **Autoloading**: Dynamically loads classes based on naming conventions
- **Single Output**: Accumulates content before sending to browser

## Security Measures

- **Centralized Request Handling**: All requests processed through index.php
- **Nginx Protection**: Rule blocks direct access to .ht* files, including lib/.ht_conf.php
- **Layered Approach**: Combines server-level and application-level security

## Benefits

- Separation of concerns
- Flexibility in output formats
- Modular and maintainable codebase
- Balanced security and convenience
- Efficient performance through single output

This architecture creates a robust, flexible, and secure foundation for web application development, suitable for various environments and scalable for larger projects.