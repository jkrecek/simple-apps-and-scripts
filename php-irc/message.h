#ifndef MESSAGE_H
#define MESSAGE_H

#include <QString>
#include "ircconstants.h"
#include <ctime>
//#include "user.h"



struct Message
{
    public:
        friend class MessageParser;

        Message();
        ~Message() {};

        QString senderNick() const { return senderNick_m; }
        //QString senderUser() const { return senderUser_m; }
        //QString senderHost() const { return senderHost_m; }
        QString senderChannel() const { return senderChannel_m; }
        QString command() const { return command_m; }
        QString content() const { return content_m; }
        int sendTime() const { return send_time_m; }
        quint8 code() const { return code_m; }
        bool isServerMessage() const { return isServerMessage_m; }
        bool isChannelMessage() const { return isChannelMessage_m; }
        bool isPing() const;
        bool isPrivate() const;

    private:
        void setSenderNick(QString senderNick) { senderNick_m = senderNick; }
        //void setSenderUser(QString senderUser) { senderUser_m = senderUser; }
        //void setSenderHost(QString senderHost) { senderHost_m = senderHost; }

        void setCommand(QString command) { command_m = command; }
        void setContent(QString content) { content_m = content; }
        void setCode(uint code) { code_m = code; }
        void setIsServerMessage(bool isServerMessage) { isServerMessage_m = isServerMessage; }
        void setIsChannelMessage(bool isChannelMessage) { isChannelMessage_m = isChannelMessage; }
        void setSendTime(int time = time(0)) { send_time_m = time; }
        void setChannel(QString channel) { senderChannel_m = channel; }

        QString senderNick_m;
        //QString senderUser_m;
        //QString senderHost_m;
        QString senderChannel_m;
        QString command_m;
        QString content_m;
        quint8 code_m;
        bool isServerMessage_m;
        bool isChannelMessage_m;
        int send_time_m;
};


#endif // MESSAGE_H
