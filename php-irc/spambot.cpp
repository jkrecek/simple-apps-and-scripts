#include "spambot.h"
#include "ircserver.h"
#include "user.h"
#include "channel.h"
#include "ircconstants.h"
#include "spambot.h"

#include <QStringList>
#include <QDebug>
#include <QFile>
#include <QTimer>
#include <iostream>
#include <ctime>
#include <math.h>

Bot::Bot(QObject* parent) : QObject(parent)
{
    // clearing current log
    QString fileName = MSG_HISTORY+"."+FILE_FORMAT;
    QFile::remove(fileName);

    // connecting to servers
    server_m = new IRCServer("irc.rizon.net", 6667);
    server_m->connectAs("phpIRC", "BOT", "BOT", "Kurva_tahnite_mi_z_nicku");

    server_m->joinChannel("#valhalla");

    qsrand(sqrt(time(0))*2);

    connect(server_m, SIGNAL(messageReceived(Message)), this, SLOT(handleReceivedMessage(Message)));

    startTime_m = time(0);

    QTimer *timer = new QTimer(this);
    connect(timer, SIGNAL(timeout()), this, SLOT(update()));
    timer->start(5000);
}

Bot::~Bot()
{
    /*QString fileName_cur = MESS_HISTORY+"."+FILE_FORMAT;
    // rename for history
    QString fileName_h = MESS_HISTORY+"_"+QString::number(startTime_m)+"."+FILE_FORMAT;

    QFile file_cur(fileName_cur);
    if (!file_cur.open(QIODevice::ReadWrite))
        return;

    file_cur.rename(fileName_h);*/
}

void Bot::handleReceivedMessage(const Message& message)
{
    // writing to log
    if (!message.senderChannel().isEmpty() && message.senderChannel().startsWith("#"))
        writeToHistoryLog(GetLogMsg(message));

    QStringList commands = message.content().split(" ");
    // rejoin on kick (TODO:: do not rejoin on every kick)
    if (message.command() == IRC::Command::Kick)
        server_m->joinChannel(message.senderChannel());

    if (!message.isServerMessage() && message.command() == IRC::Command::PrivMsg)
    {

    }
}

QString Bot::GetTimeFromUnix(int original_unix)
{
    if (!original_unix)
        original_unix = time(0);

    int hours = (original_unix/3600);
    int minutes = (original_unix/60)%60;
    int seconds = (original_unix)%60;

    // correction due to GMT
    ++hours;

    // correction due to summer time
    ++hours;

    hours = hours%24;

    QString hour = QString::number(hours);
    QString minute = (minutes < 10 ? "0" : "")+QString::number(minutes);
    QString second = (seconds < 10 ? "0" : "")+QString::number(seconds);
    QString final_time = hour+":"+minute+":"+second;

    qDebug() << final_time;

    return final_time;
}

void Bot::writeLineToFile(QString mes_str, QString fileName)
{
    QFile file(fileName);
    if (!file.open(QIODevice::WriteOnly | QIODevice::Append | QIODevice::Text))
        return;

    const char* msg = mes_str.toStdString().c_str();
    file.write(msg, qstrlen(msg));
    file.close();
}

QString Bot::GetLogMsg(const Message &message)
{
    QString time_string = GetTimeFromUnix();
    QString channel = message.senderChannel();
    QString sender = message.senderNick();
    QString msg = message.content();

    QString line;

    line = time_string+" ";
    if (!channel.isEmpty())
        line += "("+channel+") ";
    if (!sender.isEmpty())
        line += "["+sender+"] ";
    line += msg;
    line += " \n";

    return line;
}

void Bot::update()
{
    QString fileName = MSG_BUFFER+"."+FILE_FORMAT;

    QFile file(fileName);
    if (!file.open(QIODevice::ReadWrite))
        return;

    while (!file.atEnd())
    {
        QString line = file.readLine();
        if (line.isNull() || line.isEmpty())
            continue;

        QStringList msgs = line.split(";newMsg;", QString::SkipEmptyParts);
        foreach(QString msg, msgs)
            handleBufferLine(msg);
    }

    file.remove();
}

void Bot::handleBufferLine(QString line)
{
    if (line.isNull() || line.isEmpty())
        return;

    QString ch = line.left(line.indexOf(":"));
    QString channel = ch.startsWith("#") ? ch : ("#"+ch);
    if (channel.isEmpty())
        return;

    line.remove(0, line.indexOf(":")+2);

    server_m->sendMessageToChannel(channel, line);

    QString timestring = GetTimeFromUnix();

    QString historyLine = timestring+" ("+channel+") ["+server_m->ownNick()+"] "+line+" \n";
    writeToHistoryLog(historyLine);
}

void Bot::writeToHistoryLog(QString mes_str)
{
    QString fileName_cur = MSG_HISTORY+"."+FILE_FORMAT;
    QString fileName_h = MSG_HISTORY+"_"+QString::number(startTime_m)+"."+FILE_FORMAT;

    writeLineToFile(mes_str, fileName_cur);
    writeLineToFile(mes_str, fileName_h);
}
