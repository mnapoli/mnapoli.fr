---
layout: post
title: "Running GitHub Actions on a Mac Mini"
date: 2026-04-09 10:32
comments: true
image: https://mnapoli.fr/images/posts/github-actions-mac-mini.png
---

I've been hitting the 2,000 free minutes/month limit on GitHub Actions for my private repos. Instead of paying for more minutes, I set up self-hosted runners on my Mac Mini M4 running as a server at home. CI jobs are now **4x faster** and I have unlimited minutes.

<!--more-->

![](/images/posts/github-actions-mac-mini.png)

## The setup

GitHub Actions lets you [register your own machines as runners](https://docs.github.com/en/actions/concepts/runners/self-hosted-runners). The runner agent is a lightweight process that polls GitHub for jobs, runs them, and reports results. It connects outbound over HTTPS, so it works in a private network without opening any ports.

I wanted a few things:

- Multiple runners in parallel (not just one)
- Basic isolation from other user accounts on the machine (env vars, files, etc.)
- Dependency caching between runs (no re-downloading `vendor/` and `node_modules/` every time)

Note that this is **not** a full VM/container isolation. This works for me here because I am not running untrusted code in CI. This is a fully private project.

I ended up with 4 runner instances, all running under a dedicated `actions-runner` macOS user. Each runner is installed in its own directory (`runner-1` to `runner-4`) with its own workspace.

In workflows, I just use `runs-on: self-hosted`. GitHub dispatches jobs to any idle runner in the pool automatically. No need to target a specific runner.

## Alternatives I considered

**macOS VMs:** Projects like [Tart](https://github.com/cirruslabs/tart), [Tartelet](https://github.com/shapehq/tartelet), and [Cilicon](https://github.com/traderepublic/Cilicon) use Apple's Virtualization framework to run ephemeral macOS VMs. Each job gets a fresh VM, which gives you proper isolation. The problem: Apple limits you to 2 VMs at a time. I wanted 4+ parallel runners, so this was a non-starter. Also managing caches across ephemeral VMs was more work than I wanted.

**Docker containers:** Running each runner in a Linux container (e.g. [`myoung34/docker-github-actions-runner`](https://github.com/myoung34/docker-github-actions-runner)) would give isolation and parallelism. But it adds a layer of complexity: building custom images with PHP/Composer/Node, managing container lifecycles (scaling up and down containers). Too much operational overhead for my use case.

**Just paying for more minutes:** The simplest option. But at $0.008/min for Linux runners, heavy usage adds up, and the jobs are still slow on GitHub's 2 vCPU machines.

In the end, running bare runners under a dedicated macOS user was the right trade-off: simple setup, no operational overhead, and since I control 100% of the code that runs, the lack of hard isolation doesn't matter.

To be fair, I think there is room for an open-source project that could manage all that with containers, but with **auto-scaling** built in. Maybe something for the future.

## Installation

Create a dedicated macOS user:

```bash
sudo sysadminctl -addUser actions-runner -password "..." \
    -home /Users/actions-runner -shell /bin/zsh
```

Then log in as that user (e.g. `sudo su actions-runner`). Set up required tools: PHP, MySQL, Node, etc. if that's not already done globally. For me everything was already setup on the machine, so no extra work was needed!

Then download and extract the runner into N directories:

```bash
# Use the command provided by GitHub in the UI
# it might be a more recent version than this example
curl -o actions-runner.tar.gz -L https://github.com/actions/runner/releases/download/v2.333.1/actions-runner-osx-arm64-2.333.1.tar.gz

for i in 1 2 3 4; do
    mkdir -p runner-$i
    tar xzf actions-runner.tar.gz -C runner-$i
done
```

Configure each runner (get a token from **Settings → Actions → Runners**):

```bash
cd ~/runner-1
./config.sh --url https://github.com/your-org --token TOKEN
```

Each runner needs to be installed as a service so that it starts on boot. The runner's `svc.sh install` creates a LaunchAgent, but since the `actions-runner` user has no GUI session, the agent won't load. The fix is to move the plist to LaunchDaemons:

```bash
# Run this as your normal user (not actions-runner) since it needs sudo
sudo mv /Users/actions-runner/Library/LaunchAgents/actions.runner.*.plist /Library/LaunchDaemons/
sudo chown root:wheel /Library/LaunchDaemons/actions.runner.*.plist
sudo launchctl bootstrap system /Library/LaunchDaemons/actions.runner.*.plist
```

That's it. The runners start on boot and restart on crash.

## Adapting workflows

The main change is `runs-on: self-hosted` instead of `ubuntu-latest`.

I also removed:

- all `actions/cache` steps since the filesystem persists between runs (`vendor/`, `node_modules/` etc stay in place naturally)
- all `actions/setup-php` and `actions/setup-node` steps since the tools are already installed globally on the machine
- services like MySQL since the server is already running on the machine

That made the workflow files much simpler.

One thing to think about: since multiple runners share the same MySQL server, each runner needs its own test database. The `$RUNNER_NAME` environment variable is available in every job (it contains the name you set during config, e.g. `mini1`), so I use it to set the database name:

```yaml
env:
  DB_DATABASE: actions_test_${{ runner.name }}
```

## Results

| Job                          | GitHub-hosted | Self-hosted | Speedup |
|------------------------------|---------------|-------------|---------|
| Lint (Pint, PHPStan, eslint) | 5 min         | 1 min 10s   | 4.3x    |
| PHP tests                    | 6 min         | 1 min 15s   | 4.8x    |
| JS tests                     | 1 min 10s     | 30s         | 2.3x    |

The speedup comes from two things:

- the M4 chip is faster than GitHub's standard runners (2 vCPU Linux VMs)
- dependencies don't need to be downloaded/installed from scratch on every run

## Is it worth it?

This works well for my situation: I'm a solo dev, these are private repos where I'm the only contributor, and I already had a Mac Mini running 24/7. Zero extra cost, 4 parallel runners (I could probably add more), and jobs are 4x faster.

I wouldn't recommend this setup for teams though. No hard isolation between jobs, no autoscaling, and you're on your own for maintenance. If you're working with other contributors or need something more robust, I'd look into [Depot](https://depot.dev/) or [Runs-On](https://runs-on.com/), they give you faster runners with proper isolation and none of the operational overhead.
