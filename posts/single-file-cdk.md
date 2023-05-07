---
layout: post
title: "Simpler single-file AWS CDK deployments"
date: 2023-01-02 16:32
comments: true
categories:
---

I am checking out the [AWS CDK](https://docs.aws.amazon.com/cdk/v2/guide/getting_started.html) to deploy serverless applications.

But having used [Serverless Framework](https://www.serverless.com/) a lot, I find the AWS CDK very noisy and invasive in some of my projects. It doesn't have to be though.

Here are my notes on how to use the AWS CDK in a single file, without polluting your existing projects.

<!--more-->

_Disclaimer: what I am describing is only interesting is some projects, where the CDK is just "a deployment tool". I understand that some other projects can use the CDK as a framework for the whole app and the "invasiveness" is a feature._

## The standard AWS CDK setup

If you have an existing project that you want to deploy using the AWS CDK, tough luck. You can't easily "introduce" the CDK in an existing project: `cdk init` only works on an empty directory.

But let's move past that. Let's create a new TypeScript CDK project:

```bash
cdk init app --language=typescript myapp
```

Here is what we have:

```
bin/
    cdk-myapp.ts
lib/
    cdk-myapp-stack.ts
test/
    cdk-myapp.test.ts
cdk.json
README.md
package.json
package-lock.json
tsconfig.json
jest.config.js
```

With that setup, we can deploy with `npx cdk deploy`.

## Trimming the fat

If we look only at what makes the CDK actually work, we have:

```
bin/
    cdk-myapp.ts
lib/
    cdk-myapp-stack.ts
cdk.json
tsconfig.json
```

When `cdk deploy` runs, it executes the command listed in `cdk.json`:

```json
{
    "app": "npx ts-node --prefer-ts-exts bin/cdk-myapp.ts"
}
```

Cool, it uses `ts-node` to compile TypeScript on the fly. At least we don't have to run a build step and deal with compiled files. It also means we can get rid of `tsconfig.json` if we don't need it for the rest of our app.

It also means we can change the `bin/cdk-myapp.ts` file. Let's move it to the root and rename it to `cdk.ts`:

```
lib/
    cdk-myapp-stack.ts
cdk.ts
cdk.json
```

Now let's look at `cdk.ts`:

```ts
#!/usr/bin/env node
import 'source-map-support/register';
import * as cdk from 'aws-cdk-lib';
import { CdkMyappStack } from '../lib/cdk-myapp-stack';

const app = new cdk.App();
new CdkMyappStack(app, 'CdkMyappStack', {
  /* loads of comments here... */
});
```

And `lib/cdk-myapp-stack.ts`:

```ts
import * as cdk from 'aws-cdk-lib';
import { Construct } from 'constructs';

export class CdkMyappStack extends cdk.Stack {
  constructor(scope: Construct, id: string, props?: cdk.StackProps) {
    super(scope, id, props);

    // The code that defines your stack goes here
  }
}
```

Let's inline all the CDK code in `cdk.ts`:

```ts
#!/usr/bin/env node
import 'source-map-support/register';
import * as cdk from 'aws-cdk-lib';

class CdkMyappStack extends cdk.Stack {
    constructor(scope: Construct, id: string, props?: cdk.StackProps) {
        super(scope, id, props);

        // The code that defines your stack goes here
    }
}

const app = new cdk.App();
new CdkMyappStack(app, 'CdkMyappStack', {
  /* loads of comments here... */
});
```

Finally, let's inline the stack class:

```ts
#!/usr/bin/env node
import 'source-map-support/register';
import * as cdk from 'aws-cdk-lib';

const app = new cdk.App();

new class extends cdk.Stack {
    constructor(scope: Construct, id: string, props?: cdk.StackProps) {
        super(scope, id, props);

        // The code that defines your stack goes here
    }
}(app, 'my-app', {
  /* stack options */
});
```

## Final result

In the end, the AWS CDK only requires 2 files in our project:

```
cdk.ts
cdk.json
```

Those are easy to add to an existing project! We also still deploy with `npx cdk deploy` because `cdk.json` points to `cdk.ts`:

```json
{
    "app": "npx ts-node --prefer-ts-exts cdk.ts"
}
```

And finally, all our infrastructure code is defined in `cdk.ts`:

```ts
#!/usr/bin/env node
import 'source-map-support/register';
import * as cdk from 'aws-cdk-lib';

const app = new cdk.App();

new class extends cdk.Stack {
    constructor(scope: Construct, id: string, props?: cdk.StackProps) {
        super(scope, id, props);

        // The code that defines your stack goes here
    }
}(app, 'my-app', {
  /* stack options */
});
```

## Going further

We could imagine a helper function for simple apps:

```ts
#!/usr/bin/env node
import * as cdk from 'aws-cdk-lib';

cdkStack('my-app', (scope: Construct, id: string, props?: cdk.StackProps) => {
    // The code that defines your stack goes here
});
```

That `cdkStack` helper would create the `App` and the `Stack` for us.

I'm tempted to it as an open-source package… Let me know what you think of the idea!
