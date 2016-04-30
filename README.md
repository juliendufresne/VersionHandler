# Automating an application version number with Composer

## Usage

Add the following in your root composer.json file:

```json
{
    "require": {
        "juliendufresne/composer-version-handler": "^1.0"
    },
    "scripts": {
        "post-install-cmd": [
            "JulienDufresne\\VersionHandler\\ScriptHandler::updateVersion"
        ],
        "post-update-cmd": [
            "JulienDufresne\\VersionHandler\\ScriptHandler::updateVersion"
        ]
    },
    "extra": {
        "juliendufresne-version": {
            "file": "app/config/parameters.yml",
            "parameter-key": "parameters.app_version",
            "strategies": ["git", "incremental"]
        }
    }
}
```

The ``parameters.app_version`` will then be updated according to the first strategy able to deliver a version number.

## Strategies

### Git

If the current revision corresponds to a git tag, the git strategy will use this tag as a version.

### Incremental

The incremental strategy increment the version by 1.
This strategy requires the version to end with a number.
If there is no current version, the next version is set to 1.

Ex: if the current version is ``v10``, then this strategy will set the new version to ``v12``
