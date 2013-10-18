#ifndef OUTPUTER_H
#define OUTPUTER_H

#include <QString>
#include <QTime>

class Outputer
{
    public:
        Outputer();

        void Increase(int count = 1)        { current += count; Refresh();}
        void Set(int tar)                   { current = tar;    Refresh();}

        void Refresh();
        void PrintLine(QString line);
        void PrintLine(int i) { return PrintLine(QString::number(i)); }

        int getMSTime() const { return time_m->elapsed(); }

    private:
        QTime* time_m;

        int current;
};

#endif // OUTPUTER_H
