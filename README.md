# TYPO3 Extension ``quickedit``

## 1. Features

* Display of a toolbar with edit buttons for defined fields or groups of fields
* Only the defined fields are displayed in edit mode, all other fields of the page records are not shown
* Goal: Give editors a better and easier way to edit fields of page records, especially if custom page types are used (
  e.g. Events, News, Landingpage,...)

## 2. Usage

### 2.1 Installation

#### Installation using Composer

Run the following composer command:

```
composer require punktde/quickedit
```

#### Installation as extension from TYPO3 Extension Repository (TER)

Download and install the extension 'quickedit' with the extension manager module.

### 2.2 Include page TSconfig for default pages (optional)

By including the provided page TSconfig the toolbar will be available for all default page types.

## 3. Configuration

Example pageTs configuration:

```
mod {
    web_layout {
        PageTypes {
            // Default Page
            1 {
                config {
                    1 {
                        label = General
                        fields = title, subtitle
                        previewFields = title
                    }
                    2 {
                        label = Special
                        fields = slug, hidden, fe_group
                        previewFields = *
                    }
                }
            }
        }
    }
}
```

* In mod.web_layout.PageTypes use the ID of a page type to start configuration, e.g. '1' for default pages
* Inside the 'config' you can define the required button groups
  ** You need to define a 'label' to display in the backend
  ** With fields you can define one or multiple fields to edit with this button (e.g: fields=title,subtitle)
  ** The property 'previewFields' defines which fields should be previewed in the backend.
  ** If the 'previewFields' should be the same as the defined 'fields' you can use the '*'
