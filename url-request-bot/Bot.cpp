#include <cstdlib>
#include <QFile>
#include <QTextStream>
#include "Bot.h"
#include "Outputer.h"

#define LOOP_SIZE 99999
static const char* lineStart = "";
Bot::Bot(QObject *parent) : QObject(parent)
{
    u = new UrlDownloader();

    // bufferup downloader
    for(uint i = 0; i < LOOP_SIZE; ++i)
        u->Download(QString(lineStart)+QString::number(i));
}


