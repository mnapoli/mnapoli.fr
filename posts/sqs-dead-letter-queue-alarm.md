---
layout: post
title: "Email alerts on SQS dead letter queues"
date: 2020-10-07 13:00
comments: true
tags:
    - serverless
---

> **Update:** are you using Serverless Framework? Check out Lift, a plugin that deploys pre-configured SQS queues, Lambda workers, and [SQS DLQ with email alerts](https://github.com/getlift/lift/blob/master/docs/queue.md#how-it-works).

When running asynchronous tasks on AWS, it often makes sense to send failed tasks to an SQS "_Dead Letter Queue_".

A dead letter queue is simply a standard SQS queue that we create to store those failed tasks.

All that is great, but what do we do with messages in that special queue? It doesn't make sense to process them again, since we know our code fails at that. **What we want instead is get alerted, so that we can inspect those messages and debug the error.**

<!--more-->

It is possible to set up email alerts whenever there are messages in the queue. That is doable via a CloudWatch alarm on the "queue size" metric.

As soon as the queue is not empty, the alarm triggers and sends an email via SNS.

It's not easy to find a complete CloudFormation example online, so here it one that I wrote while working on [7777](https://port7777.com/):

```yaml
AWSTemplateFormatVersion: '2010-09-09'
Resources:

    Queue:
        Type: AWS::SQS::Queue
        Properties:
            RedrivePolicy:
                # Jobs will be retried 3 times
                maxReceiveCount: 3
                # And if they still fail, they'll got to the dead letter queue
                deadLetterTargetArn: !GetAtt DeadLetterQueue.Arn
    # Failed jobs from the Queue above will end up in this queue
    # (that's the dead letter queue)
    DeadLetterQueue:
        Type: AWS::SQS::Queue

    DlqAlarm:
        Type: AWS::CloudWatch::Alarm
        Properties:
            AlarmName: My-DLQ
            AlarmDescription: 'There are failed messages in the dead letter queue.'
            Namespace: AWS/SQS
            MetricName: ApproximateNumberOfMessagesVisible
            Dimensions:
                -   Name: QueueName
                    Value: !GetAtt DeadLetterQueue.QueueName
            Statistic: Sum
            Period: 60
            EvaluationPeriods: 1
            Threshold: 0
            ComparisonOperator: GreaterThanThreshold
            AlarmActions:
                - !Ref DlqAlarmEmail

    DlqAlarmEmail:
        Type: AWS::SNS::Topic
        Properties:
            Subscription:
                -   Endpoint: me@example.com
                    Protocol: email
```
