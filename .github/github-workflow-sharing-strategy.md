# GitHub Workflow Sharing Strategy

## Overview

This document outlines a progressive approach to sharing GitHub workflow files across repositories, specifically for Drupal module testing but applicable to any similar use case where workflows need to be shared with room for customization.

## The Progressive Approach

### Phase 1: Start with Individual Workflows
- Write workflows from scratch in your first repository
- Get it working and tested
- Understand the specific requirements and edge cases

### Phase 2: Create Workflow Templates
Once you have a working solution and need to apply it to additional repos:

1. Create a `.github` repository in your organization (if it doesn't exist)
2. Add workflow templates that new repos can use as starting points
3. Teams copy the template and customize as needed for their specific requirements

**Benefits:**
- Each repo gets its own independent copy
- Full flexibility to customize without breaking other repos
- No runtime dependencies on external repositories
- Easy for developers to understand (everything is visible)

**Repository Structure:**
```
your-org/.github/
├── workflow-templates/
│   ├── drupal-module-test.yml                    # The workflow template
│   ├── drupal-module-test.properties.json        # Metadata (optional)
│   └── drupal-module-test.svg                    # Icon (optional)
```

**Example Template:**
```yaml
name: Drupal Module Tests

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v4
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
          extensions: mbstring, xml, ctype, iconv, mysql
      
      - name: Install Drupal with Profile
        working-directory: drupal-test
        run: |
          mv .ddev ../ddev-backup
          rm -rf *
          git clone https://github.com/University-of-Denver/drupal-composer-managed.git .
      
      # TODO: Add project-specific module installation here
      
      - name: Install module
        run: |
          composer require drupal/${{ github.event.repository.name }}
      
      # TODO: Add any custom setup steps here
      
      - name: Run PHPUnit
        run: |
          vendor/bin/phpunit web/modules/contrib/${{ github.event.repository.name }}
      
      - name: Run PHPCS
        run: |
          vendor/bin/phpcs --standard=Drupal web/modules/contrib/${{ github.event.repository.name }}
```

**Properties File Example:**
```json
{
  "name": "Drupal Module Testing Workflow",
  "description": "Standard testing workflow for Drupal modules",
  "iconName": "drupal-module-test",
  "categories": ["PHP", "Testing", "Drupal"],
  "filePatterns": [".*\\.module$", "composer\\.json$"]
}
```

**How to Use:**
1. Navigate to **Actions** tab in any repository
2. Click **New workflow**
3. Organization templates appear at the top
4. Click **Configure** to copy the template
5. Customize as needed for your specific project

### Phase 3: Extract Common Patterns to Composite Actions
After applying templates to multiple repos, identify steps that are:
- Truly identical across repositories
- Stable (not frequently customized)
- Updated frequently (bug fixes, improvements)

These are candidates for composite actions.

**Create Composite Actions for:**
- Standard Drupal environment setup
- DDEV configuration and startup
- Common test execution patterns
- Code quality checks
- Module installation procedures

**Example Composite Action Structure:**
```
your-org/github-actions/
├── drupal-setup/
│   └── action.yml
├── ddev-start/
│   └── action.yml
├── run-phpunit/
│   └── action.yml
└── install-module/
    └── action.yml
```

**Example Composite Action:**
```yaml
# your-org/github-actions/drupal-setup/action.yml
name: 'Setup Drupal Test Environment'
description: 'Sets up a full Drupal site for module testing'

inputs:
  profile_repo:
    description: 'Drupal profile repository URL'
    required: true
  php_version:
    description: 'PHP version to use'
    required: false
    default: '8.1'

runs:
  using: "composite"
  steps:
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: ${{ inputs.php_version }}
        extensions: mbstring, xml, ctype, iconv, mysql
    
    - name: Install Drupal with Profile
      shell: bash
      working-directory: drupal-test
      run: |
        mv .ddev ../ddev-backup
        rm -rf *
        git clone ${{ inputs.profile_repo }} .
```

**Using Composite Actions in Workflows:**
```yaml
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      
      # Use shared action for standard setup
      - uses: your-org/github-actions/drupal-setup@v1
        with:
          profile_repo: 'https://github.com/University-of-Denver/drupal-composer-managed.git'
      
      # Project-specific customization
      - name: Install additional dependencies
        run: composer require drupal/token drupal/pathauto
      
      - name: Install this module
        run: composer require drupal/${{ github.event.repository.name }}
      
      # Custom setup steps
      - name: Custom database setup
        run: drush sql-query --file=custom-schema.sql
      
      # Use shared action for testing
      - uses: your-org/github-actions/run-phpunit@v1
        with:
          module_name: ${{ github.event.repository.name }}
```

### Phase 4: Update Templates to Use Composite Actions
Once composite actions are created, update your workflow templates to reference them:

```yaml
steps:
  - uses: actions/checkout@v4
  
  # Use composite action instead of inline steps
  - uses: your-org/github-actions/drupal-setup@v1
    with:
      profile_repo: 'https://github.com/University-of-Denver/drupal-composer-managed.git'
  
  # TODO: Add project-specific steps here
  
  - uses: your-org/github-actions/install-module@v1
  
  # TODO: Add custom pre-test setup
  
  - uses: your-org/github-actions/run-phpunit@v1
```

## Comparison of Approaches

### Workflow Templates
**Use When:**
- Setting up new repositories
- Need for significant customization per project
- Want teams to have full control over their workflows

**Pros:**
- ✅ Full customization freedom
- ✅ No runtime dependencies
- ✅ Everything visible in the repo
- ✅ Perfect for "mostly similar" workflows

**Cons:**
- ❌ No automatic updates
- ❌ Can drift over time
- ❌ Manual updates needed for bug fixes

### Composite Actions
**Use When:**
- Steps are truly identical across repos
- Need to update behavior across many repos at once
- Have stable, well-understood patterns

**Pros:**
- ✅ Automatic updates across all repos
- ✅ Centralized maintenance
- ✅ Consistent execution
- ✅ True code reuse (DRY)

**Cons:**
- ❌ Less flexibility for customization
- ❌ Runtime dependency on shared repo
- ❌ Abstraction can hide details

### Reusable Workflows
**Use When:**
- Have a subset of truly identical workflows
- Need job-level configuration sharing
- Want to enforce consistency

**Pros:**
- ✅ Share entire job configurations
- ✅ Enforce standardization

**Cons:**
- ❌ Very rigid, hard to customize
- ❌ Variation explosion (too many inputs)
- ❌ All-or-nothing approach
- ❌ **Not recommended for this use case**

## Implementation Plan

### Step 1: Create Your Template Repository
1. Create or use existing `.github` repository in your organization
2. Set visibility to "Internal" (GitHub Enterprise feature)
3. Create `workflow-templates/` directory

### Step 2: Develop Initial Template
1. Take your best working workflow
2. Add TODO comments where customization is expected
3. Use clear naming and comments
4. Create properties file for better discovery

### Step 3: Roll Out Templates
1. Apply template to 3-5 repositories
2. Gather feedback on what needs customization
3. Iterate on the template based on real usage

### Step 4: Identify Abstraction Candidates
Monitor for:
- Steps that are copied identically across repos
- Steps that need bug fixes in multiple places
- Steps that are stable and rarely customized

### Step 5: Create Composite Actions
1. Create a shared repository for actions (e.g., `your-org/github-actions`)
2. Extract common patterns one at a time
3. Version your actions (use tags: `v1`, `v1.0.0`)
4. Update template to reference new actions

### Step 6: Maintain and Evolve
- Keep templates updated for new projects
- Refine composite actions based on usage
- Use Renovate or Dependabot to update action versions
- Document patterns and decisions

## GitHub Enterprise Benefits

With GitHub Enterprise, you can:
- Use **internal repositories** for templates and actions (visible to org, not public)
- Better access control and security
- Organization-wide visibility of templates
- Audit logs for workflow usage

## Additional Resources

### Official GitHub Documentation
- [Creating workflow templates](https://docs.github.com/en/actions/using-workflows/creating-starter-workflows-for-your-organization)
- [Creating composite actions](https://docs.github.com/en/actions/creating-actions/creating-a-composite-action)
- [Reusing workflows](https://docs.github.com/en/actions/using-workflows/reusing-workflows)
- [About GitHub Actions](https://docs.github.com/en/actions/learn-github-actions/understanding-github-actions)

### Best Practices
- [Security hardening for GitHub Actions](https://docs.github.com/en/actions/security-guides/security-hardening-for-github-actions)
- [GitHub Actions usage limits](https://docs.github.com/en/actions/learn-github-actions/usage-limits-billing-and-administration)

## Key Takeaways

1. **Start simple**: Begin with individual workflows, then templates
2. **Let patterns emerge**: Don't abstract prematurely
3. **Copy-paste is okay**: It reveals the actual pain points
4. **Abstract when painful**: Create composite actions when you're maintaining the same code in many places
5. **Templates + Actions = Flexibility**: Use both together for maximum benefit
6. **Keep customization easy**: Don't force everything into shared code

The goal is maintainability through the right balance of standardization and flexibility.
