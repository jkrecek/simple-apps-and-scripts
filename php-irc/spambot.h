#ifndef SPAMBOT_H
#define SPAMBOT_H

#include <QObject>
#include "message.h"
#include "user.h"
#include <QStringList>
#include <QMap>

class IRCServer;

const QString MSG_HISTORY = "msgHistory";
const QString MSG_BUFFER = "msgBuffer";
const QString FILE_FORMAT = "txt";

class Bot : public QObject
{
    Q_OBJECT

    public:
        Bot(QObject* parent = 0);
        ~Bot();

        void Kick(User* user, QString reason);
        void GenerateNewKey();

        QList<User*> Users;

        void writeLineToFile(QString mes_str, QString fileName);
        void writeToHistoryLog(QString mes_str);
        QString GetLogMsg(const Message& message);

    private slots:
        void handleReceivedMessage(const Message& message);
        void update();
        void handleBufferLine(QString line);
        QString GetTimeFromUnix(int original_unix = 0);

    private:
        IRCServer* server_m;

        int startTime_m;
};
#endif
